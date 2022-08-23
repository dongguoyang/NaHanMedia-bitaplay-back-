<?php


namespace App\Srv\Provider;


use App\Models\AndroidShop;
use App\Models\AppCategory;
use App\Models\AppGrade;
use App\Models\Area;
use App\Models\Industry;
use App\Models\System;
use App\Srv\Srv;

class ToolSrv extends Srv
{

    public function appCategory()
    {
        $categories = AppCategory::with('child', 'child.child')->where('pid', 0)->get()->toArray();
        $data = [];
        foreach ($categories as $k1 => $v1) {
            $data[$k1]['value'] = $v1['id'];
            $data[$k1]['label'] = $v1['name'];
            $v2Data = [];
            foreach ($v1['child'] as $k2 => $v2) {
                $v2Data[$k2]['value'] = $v2['id'];
                $v2Data[$k2]['label'] = $v2['name'];
                $v3Data = [];
                foreach ($v2['child'] as $k3 => $v3) {
                    $v3Data[$k3]['value'] = $v3['id'];
                    $v3Data[$k3]['label'] = $v3['name'];
                }
                $v2Data[$k2]['children'] = $v3Data;
            }
            $data[$k1]['children'] = $v2Data;
        }
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function appGrade()
    {
        $data = AppGrade::get();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function androidShop()
    {
        $data = AndroidShop::get();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function price()
    {
        $lowestReward = System::where('key', 'lowest_download_reward')->first();
        $thirdLoginPrice = System::where('key', 'third_login_price')->first();
        $fuelPrice = System::where('key', 'fuel_price')->first();
        $data['fuel_price'] = $fuelPrice['value']['price'];
        $data['third_login_price'] = $thirdLoginPrice['value']['price'];
        $data['lowest_download_reward'] = $lowestReward['value']['amount'];
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function system()
    {
        $data = [];
        $systems = System::whereIn('key', ['fee'])->get();
        foreach ($systems as $v) {
            $data[$v['key']] = $v['value'];
        }
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function area()
    {
        $list = Area::where('pid', 0)->with('child')->get();
        $data = [];
        foreach ($list as $v) {
            $item = ['label' => $v['name'], 'options' => []];
            foreach ($v['child'] as $cv) {
                $cItem = ['value' => $cv['name'], 'label' => $cv['name']];
                $item['options'][] = $cItem;
            }
            $data[] = $item;
        }
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function industry()
    {
        $data = Industry::get();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }
}
