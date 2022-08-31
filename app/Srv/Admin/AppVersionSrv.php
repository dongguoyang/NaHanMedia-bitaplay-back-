<?php
namespace App\Srv\Admin;

use App\Srv\Srv;
use App\Models\AppVersion;

class AppVersionSrv extends Srv{

    public function list($p){
        $query = AppVersion::query();
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['version']) {
            $query->where('version', 'like', "%{$p['version']}%");
        }

        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));

    }

    public function getAppVersion(){
        $query = AppVersion::query();
        $query->where('status', APP_VERSION_STATUS_YES);
        $data = $query->orderByDesc('version')->first();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function editStatus($id)
    {
        if (!$AppVersion = AppVersion::where('id', $id)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $status = APP_VERSION_STATUS_NO;
        if ($AppVersion['status'] == APP_VERSION_STATUS_NO) {
            //同版本指允许一个启用的安装包
            if ($AppVersionHistory = AppVersion::where(['version' => $AppVersion['version'], 'status' => APP_VERSION_STATUS_YES])->first()) {
                if ($AppVersionHistory['id'] != $id) {
                    return $this->returnData(ERR_PARAM_ERR, '已存在该版本的包');
                }
            }
            $status = APP_VERSION_STATUS_YES;
        }
        $AppVersion->status = $status;
        $AppVersion->save();
        return $this->returnData();
    }

    public function save($p){
        if (AppVersion::where(['version' => $p['version'], 'status' => APP_VERSION_STATUS_YES])->count()) {
            return $this->returnData(ERR_PARAM_ERR, '已存在该版本的包');
        }
        AppVersion::create(
            [
                'url' => $p['url'],
                'version'=>$p['version'],
                'remark'=>$p['remark'],
                'status'=>$p['status']
            ]
        );
        return $this->returnData();
    }
}
