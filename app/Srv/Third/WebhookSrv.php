<?php


namespace App\Srv\Third;

use App\Models\Provider;
use App\Models\ProviderApp;
use App\Models\ProviderAppUsers;
use App\Models\ProviderInfo;
use App\Models\ProviderWithdraw;
use App\Models\User;
use App\Models\UserThirdLoginRecord;
use App\Models\UserWithdraw;
use App\Srv\Admin\ProviderFinanceSrv;
use App\Srv\Admin\UserFinanceSrv;
use App\Srv\MyException;
use App\Srv\Provider\RemindSrv;
use App\Srv\Provider\WalletSrv;
use App\Srv\Srv;
use App\Srv\Utils\AlipaySrv;
use App\Srv\Utils\SmsSrv;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookSrv extends Srv
{
    public function alipay($number)
    {
        $res = (new AlipaySrv())->checkTransToAlipayAccount($number);
        if ($res['code'] != 0) {
            return $res;
        }
        $res = $res['data'];
        $p = [
            'status' => $res['status'],
            'evidence' => $res['payment_number'],
            'refuse_reason' => $res['status'] == WITHDRAW_STATUS_DISABLE ? '支付宝转账失败' : ''
        ];
        if (Str::startsWith($number, 'PW')) {
            if (!$withdraw = ProviderWithdraw::where('number', $number)->first()) {
                return $this->returnData();
            }
            $p['id'] = $withdraw->id;
            return (new ProviderFinanceSrv())->doWithdraw($p);
        } else if (Str::startsWith($number, 'UW')) {
            if (!$withdraw = UserWithdraw::where('number', $number)->first()) {
                return $this->returnData();
            }
            $p['id'] = $withdraw->id;
            return (new UserFinanceSrv())->doWithdraw($p);
        }
        return $this->returnData();
    }
}




