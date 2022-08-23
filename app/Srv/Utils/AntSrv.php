<?php


namespace App\Srv\Utils;

use App\Srv\Srv;
use Illuminate\Support\Facades\Http;

class AntSrv extends Srv
{
    private $privateKeyPath;
    private $id;
    private $keyId;
    private $account;
    private $baseUrl;
    private $bizId;
    private $contractName;
    private $tenantId;
    private $gas;

    public function __construct()
    {
        $this->privateKeyPath = storage_path('certs/' . env('ANT_PRIVATE_NAME'));
        $this->id = env('ANT_ID');
        $this->keyId = env('ANT_KEY_ID');
        $this->account = env('ANT_ACCOUNT');
        $this->baseUrl = env('ANT_BASE_URL');
        $this->bizId = env('ANT_BIZ_ID');
        $this->contractName = env('ANT_CONTRACT');
        $this->tenantId = env('ANT_TENANT_ID');
        $this->gas = env('ANT_GAS');
    }

    private function shakeHand()
    {
        // 加密秘钥
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        if (!$privateKey) {
            return $this->returnData(ERR_FAILED, '私钥错误');
        }
        // 待加密信息
        $now = time() * 1000;
        $message = $this->id . $now;
        // 加密
        if (!openssl_sign($message, $secret, $privateKey, OPENSSL_ALGO_SHA256)) {
            return $this->returnData(ERR_FAILED, '加密失败');
        }
        $secret = bin2hex($secret);
        // 获取TOKEN
        $res = Http::post("{$this->baseUrl}/api/contract/shakeHand", [
            'accessId' => $this->id,
            'time' => $now,
            'secret' => $secret
        ]);
        $res = json_decode($res->body(), true);
        if (isset($res['success']) && $res['success'] == true) {
            return $this->returnData(ERR_SUCCESS, '', $res['data']);
        } else {
            return $this->returnData(ERR_FAILED, $res['message']);
        }
    }

    public function chainCallForBiz(array $p)
    {
        $token = $this->shakeHand();
        if ($token['code'] != ERR_SUCCESS) {
            return $token;
        }
        $p['token'] = $token['data'];
        $p['orderId'] = time() . rand(10000000, 99999999);
        $p['bizid'] = $this->bizId;
        $p['account'] = $this->account;
        $p['contractName'] = $this->contractName;
        $p['mykmsKeyId'] = $this->keyId;
        $p['method'] = 'CALLCONTRACTBIZASYNC';
        $p['accessId'] = $this->id;
        $p['gas'] = $this->gas;
        $p['tenantid'] = $this->tenantId;
        // 开始调用合约
        $res = Http::post("{$this->baseUrl}/api/contract/chainCallForBiz", $p);
        $res = json_decode($res->body(), true);
        if (isset($res['success']) && $res['success'] == true) {
            return $this->returnData(ERR_SUCCESS, '', $res['data']);
        } else {
            return $this->returnData(ERR_FAILED, '上链失败');
        }
    }


    // 用户信息上链
    public function userToChain($p)
    {
        $p['methodSignature'] = 'insertUser(string,string,string,string,string,string,string,string)';
        $p['inputParamListStr'] = "['{$p['code']}','{$p['name']}','{$p['id_number']}','{$p['educational']}','{$p['industry']}','{$p['occupation']}','{$p['local']}','{$p['addr']}']";
        $p['outTypes'] = '[]';
        $res = $this->chainCallForBiz($p);
        if ($res['code'] != ERR_SUCCESS) {
            return $res;
        }
        return $this->returnData(ERR_SUCCESS, '', $res['data']);
    }


    // 软件商信息上链
    public function providerToChain($p)
    {
        $p['methodSignature'] = 'insertProvider(string,string,string)';
        $p['inputParamListStr'] = "['{$p['id']}','{$p['name']}','{$p['content']}']";
        $p['outTypes'] = '[]';
        $res = $this->chainCallForBiz($p);
        if ($res['code'] != ERR_SUCCESS) {
            return $res;
        }
        return $this->returnData(ERR_SUCCESS, '', $res['data']);
    }


    // 用户信息修改
    public function updateUserToChain($p)
    {
        $p['methodSignature'] = 'updateUser(string,string,string,string,string,string,string,string)';
        $p['inputParamListStr'] = "['{$p['code']}','{$p['name']}','{$p['id_number']}','{$p['educational']}','{$p['industry']}','{$p['occupation']}','{$p['local']}','{$p['addr']}']";
        $p['outTypes'] = '[]';
        $res = $this->chainCallForBiz($p);
        if ($res['code'] != ERR_SUCCESS) {
            return $res;
        }
        return $this->returnData(ERR_SUCCESS, '', $res['data']);
    }

    public function createTxId()
    {
        $str = "0123456789abcdef";
        $txId = "";
        for ($i = 0; $i < 64; $i++) {
            $txId .= $str[rand(0, 15)];
        }
        return $txId;
    }
}
