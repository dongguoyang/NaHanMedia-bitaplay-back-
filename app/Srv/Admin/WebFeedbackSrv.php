<?php


namespace App\Srv\Admin;


use App\Models\WebFeedback;
use App\Srv\Srv;

class WebFeedbackSrv extends Srv
{
    public function list($status)
    {
        $query = WebFeedback::orderByDesc('id');
        if ($status > 0) {
            $query->where('status', $status);
        }
        $list = $query->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }


    public function handel($id, $remark)
    {
        if (!$feedback = WebFeedback::where('id', $id)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $feedback->remark = $remark;
        $feedback->status = 2;
        $feedback->save();
        return $this->returnData();
    }
}
