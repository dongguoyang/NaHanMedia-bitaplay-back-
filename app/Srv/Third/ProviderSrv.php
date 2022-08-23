<?php


namespace App\Srv\Third;

use App\Models\ProviderApp;
use App\Models\ProviderAppChainRecord;
use App\Models\User;
use App\Srv\MyException;
use App\Srv\Provider\RemindSrv;
use App\Srv\Srv;
use App\Srv\Utils\AntSrv;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProviderSrv extends Srv
{
    public function toChain($p)
    {
        try {
            DB::beginTransaction();
            if (!$app = ProviderApp::where('app_key', $p['app_key'])->lockForUpdate()->first()) {
                return $this->returnData(ERR_PARAM_ERR, 'APP KEY错误');
            }
            // 解密
            $data = $this->decrypt($p['data'], $app->app_secret);
            if (!$data) {
                throw new MyException('解密失败');
            }
            $data = json_decode($data, true);
            if (!isset($data['content']) || !isset($data['app_id']) || $data['app_id'] != $app->app_id) {
                throw new MyException('加密信息错误');
            }
            if (strlen($data['content']) != 32) {
                throw new MyException('上链信息只接受32位长度的字符串');
            }
            // 预估燃料
            $fuel = env('ANT_GAS') + rand(-2000, 2000);
            if ($app->fuel_amount < $fuel) {
                throw new MyException('燃料不足，请充值');
            }
            $remind = false;
            $fuelAmount = $app->fuel_amount;
            $remindAmount = $app->remind_fuel;
            if ($remindAmount > 0 && $fuelAmount >= $remindAmount && $fuelAmount - $fuel < $remindAmount) {
                $remind = true;
            }
            // 软件商信息
            $provider = $app->provider;
            if ($provider->status != PROVIDER_STATUS_ABLE) {
                throw new MyException('已禁用');
            }
            $info = $provider->info;
            // 上链
            $p['content'] = $data['content'];
            $p['id'] = $provider->code;
            $p['name'] = $info->name;
            $res = (new AntSrv())->providerToChain($p);
            if ($res['code'] != ERR_SUCCESS) {
                throw new MyException('上链失败');
            }
            // 更新燃料
            $app->fuel_amount -= $fuel;
            $app->save();
            // 写上链记录
            ProviderAppChainRecord::create([
                'provider_app_id' => $app->id,
                'number' => 'TC' . time() . rand(100000, 999999),
                'content' => $data['content'],
                'hash' => $res['data'],
                'fuel' => $fuel
            ]);
            DB::commit();

            // 发送短信
            if ($remind) {
                (new RemindSrv())->sendRemindProvider($app->provider_id, '上链功能', $remindAmount);
            }

            return $this->returnData(ERR_SUCCESS, '', ['fuel' => $fuel, 'hash' => $res['data']]);
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, '服务器错误');
        }

    }

}

