<?php


namespace App\Srv\Admin;


use App\Models\Admin;
use App\Models\Provider;
use App\Models\ProviderInfo;
use App\Srv\Srv;

class ProviderSrv extends Srv
{
    public function list($p)
    {
        $query = Provider::with('info', 'wallet')
            ->withCount(['app' => function ($query) {
                $query->where('status', APP_STATUS_ABLE);
            }]);
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['tel']) {
            $query->where('tel', 'like', "%{$p['tel']}%");
        }
        if ($p['id'] > 0) {
            $query->where('id', $p['id']);
        }
        if ($p['info_status'] > -1) {
            if ($p['info_status'] == PROVIDER_INFO_STATUS_NOT) {
                $query->doesntHave('info');
            } else {
                $query->whereHas('info', function ($query) use ($p) {
                    $query->where('status', $p['info_status']);
                });
            }
        }
        $list = $query->orderBy('id', 'desc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function editStatus($id)
    {
        if (!$provider = Provider::where('id', $id)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $provider->status = $provider->status == PROVIDER_STATUS_DISABLE ? PROVIDER_STATUS_ABLE : PROVIDER_STATUS_DISABLE;
        $provider->save();
        return $this->returnData();
    }

    public function info($p)
    {
        $query = ProviderInfo::with('provider');
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['tel']) {
            $query->whereHas('provider', function ($query) use ($p) {
                $query->where('tel', 'like', "%{$p['tel']}%");
            });
        }
        if ($p['provider_id'] > 0) {
            $query->where('provider_id', $p['provider_id']);
        }

        $list = $query->orderBy('id', 'desc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function editInfoStatus($p)
    {
        if (!$info = ProviderInfo::where('id', $p['id'])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        if ($info->status != PROVIDER_INFO_STATUS_PENDING) {
            return $this->returnData(ERR_PARAM_ERR, '已审核');
        }
        $p['refuse_reason'] = $p['refuse_reason'] ?: '';
        ProviderInfo::where('id', $p['id'])->update($p);
        return $this->returnData();
    }
}
