<?php


namespace App\Srv\Admin;


use App\Models\AppCategory;
use App\Srv\Srv;

class CategorySrv extends Srv
{
    public function list($name)
    {
        $query = AppCategory::with('parent', 'parent.parent');
        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function add($pid, $name)
    {
        if (AppCategory::where('name', $name)->count() > 0) {
            return $this->returnData(ERR_PARAM_ERR, '分类已添加');
        }
        if ($pid > 0) {
            if (AppCategory::where('id', $pid)->count() == 0) {
                return $this->returnData(ERR_PARAM_ERR, '非法操作');
            }
        }
        AppCategory::create([
            'pid' => $pid ? $pid : 0,
            'name' => $name
        ]);
        return $this->returnData();
    }

    public function tree()
    {
        $data = AppCategory::where('pid', 0)->with('child')->get();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function cascaderTree()
    {
        $data = [];
        $list = AppCategory::where('pid', 0)->with('child')->get();
        foreach ($list as $k => $v) {
            $data[$k] = [
                'value' => $v['id'],
                'label' => $v['name'],
                'children' => [],
            ];
            foreach ($v['child'] as $ck => $cv) {
                $data[$k]['children'][$ck] = [
                    'value' => $cv['id'],
                    'label' => $cv['name'],
                ];
            }
        }
        return $this->returnData(ERR_SUCCESS, '', $data);
    }
}
