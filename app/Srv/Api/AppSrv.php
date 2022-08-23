<?php


namespace App\Srv\Api;


use App\Models\ProviderApp;
use App\Models\ProviderAppCategory;
use App\Models\ProviderAppUser;
use App\Models\SearchWord;
use App\Models\User;
use App\Models\UserCollect;
use App\Models\UserDevice;
use App\Models\UserDownloadCategory;
use App\Models\UserDownloadRecord;
use App\Models\UserInfo;
use App\Models\UserWallet;
use App\Models\UserWalletRecord;
use App\Srv\MyException;
use App\Srv\Provider\RemindSrv;
use App\Srv\Srv;
use App\Srv\Utils\RecognizeIdCardSrv;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppSrv extends Srv
{
    private function _list($p)
    {
        $user = User::where('id', $p['uid'])->first();

        $query = ProviderApp::select('id', 'name', 'icon', 'ios', 'web', 'ios_package_name', 'package_name', 'is_download_reward', 'download_reward', 'reward_amount', 'desc', 'download_reward_status', 'provider_id', 'download_count')
            ->with('shop')
            ->withCount(['download as real_download_count', 'collect' => function ($query) {
                $query->where('status', 2);
            }])
            ->where('status', APP_STATUS_ABLE);

        if ($p['name']) {
            $query->where(function ($query) use ($p) {
                $query->where('name', 'like', "%{$p['name']}%")
                    ->orWhereHas('l2', function ($query) use ($p) {
                        $query->where('name', 'like', "%{$p['name']}%");
                    })->orWhereHas('l3', function ($query) use ($p) {
                        $query->where('name', 'like', "%{$p['name']}%");
                    });
            });
        }


        if (!$user) {
            $query->where(function ($query) {
                $query->where('is_download_reward', 0)
                    ->orWhere('download_reward_status', 0);
            });
        }

        if ($user) {
            // 精准推荐
            $query->where(function ($query) use ($user) {

                $info = $user->info;
                // 地区
                $query->where(function ($query) use ($info) {
                    $query->where('recommend_city', 0);
                    if ($info) {
                        if ($info && $info->city) {
                            $query->orWhereNotNull(DB::raw("find_in_set('{$info->city}',recommend_city)"));
                        }
                    }
                });

                // 行业
                $query->orWhere(function ($query) use ($info) {
                    $query->where('recommend_industry', 0);
                    if ($info) {
                        if ($info && $info->industry_id) {
                            $query->orWhereNotNull(DB::raw("find_in_set({$info->industry_id},recommend_industry)"));
                        }
                    }
                });

                // 年龄
                $query->orWhere(function ($query) use ($info) {
                    $query->where('recommend_age', 0);
                    if ($info) {
                        if ($info && $info->age) {
                            $query->orWhereNotNull(DB::raw("find_in_set({$info->age},recommend_age)"));
                        }
                    }
                });

                // 性别
                $query->orWhere(function ($query) use ($info) {
                    $query->where('recommend_sex', 0);
                    if ($info) {
                        if ($info && $info->sex) {
                            $query->orWhere('recommend_sex', $info->sex);
                        }
                    }
                });

                // 系统
                $device = UserDevice::where('user_id', $user->id)->orderBy('id', 'desc')->first();
                $query->orWhere(function ($query) use ($device) {
                    $query->where('recommend_system', 0)
                        ->orWhere('recommend_system', $device['name']);

                });

                // // 最近7天下载分类
                // $category = UserDownloadCategory::where('user_id', $user->id)
                //     ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 7 * 24 * 3600))
                //     ->where('created_at', '>=', date('Y-m-d H:i:s'))
                //     ->get();
                // $l3 = [];
                // foreach ($category as $v) {
                //     if (!in_array($v['l3'], $l3)) {
                //         $l3[] = $v['l3'];
                //     }
                // }
                // if (count($l3) > 0) {
                //     $query->where(function ($query) use ($l3) {
                //         $query->where(function ($query) use ($l3) {
                //             $query->where('recommend_week_download', 0);
                //             $query->orWhereHas('category', function ($query) use ($l3) {
                //                 $query->whereIn('l3', $l3);
                //             });
                //         })->orWhere(function ($query) use ($l3) {
                //             $query->where('recommend_preference', 0);
                //             $query->orWhereHas('recommendCategory', function ($query) use ($l3) {
                //                 $query->whereIn('l3', $l3);
                //             });
                //         });
                //     });
                // }
                //
                //
                // // 最近一月下载分类
                // $category = UserDownloadCategory::where('user_id', $user->id)
                //     ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 30 * 24 * 3600))
                //     ->where('created_at', '>=', date('Y-m-d H:i:s'))
                //     ->get();
                // $l3 = [];
                // foreach ($category as $v) {
                //     if (!in_array($v['l3'], $l3)) {
                //         $l3[] = $v['l3'];
                //     }
                // }
                // if (count($l3) > 0) {
                //     $query->where(function ($query) use ($l3) {
                //         $query->where(function ($query) use ($l3) {
                //             $query->where('recommend_month_download', 0);
                //             $query->orWhereHas('category', function ($query) use ($l3) {
                //                 $query->whereIn('l3', $l3);
                //             });
                //         })->orWhere(function ($query) use ($l3) {
                //             $query->where('recommend_preference', 0);
                //             $query->orWhereHas('recommendCategory', function ($query) use ($l3) {
                //                 $query->whereIn('l3', $l3);
                //             });
                //         });
                //     });
                // }

                // 下载推荐
                // 最近7天下载分类
                $weekCategory = UserDownloadCategory::where('user_id', $user->id)
                    ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 7 * 24 * 3600))
                    ->where('created_at', '>=', date('Y-m-d H:i:s'))
                    ->get();
                // 最近一月下载分类
                $monthCategory = UserDownloadCategory::where('user_id', $user->id)
                    ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 30 * 24 * 3600))
                    ->where('created_at', '>=', date('Y-m-d H:i:s'))
                    ->get();
                // 处理筛选条件
                $weekL3 = [];
                foreach ($weekCategory as $v) {
                    if (!in_array($v['l3'], $weekL3)) {
                        $weekL3[] = $v['l3'];
                    }
                }
                $monthL3 = [];
                foreach ($monthCategory as $v) {
                    if (!in_array($v['l3'], $monthL3)) {
                        $monthL3[] = $v['l3'];
                    }
                }
                // 开始筛选
                if (count($weekL3) > 0 || count($monthL3) > 0) {
                    $query->where(function ($query) use ($weekL3, $monthL3) {
                        // 筛选7天
                        if (count($weekL3) > 0) {
                            $query->orWhere(function ($query) use ($weekL3) {
                                $query->where('recommend_category_cycle', 1)
                                    ->orWhereHas('recommendCategory', function ($query) use ($weekL3) {
                                        $query->whereIn('l3', $weekL3);
                                    });
                            });
                        }
                        // 筛选1月
                        if (count($monthL3) > 0) {
                            $query->orWhere(function ($query) use ($monthL3) {
                                $query->where('recommend_category_cycle', 2)
                                    ->orWhereHas('recommendCategory', function ($query) use ($monthL3) {
                                        $query->whereIn('l3', $monthL3);
                                    });
                            });
                        }
                        $query->orWhere('recommend_category_cycle', 0);
                    });
                }
            });
        }

        if ($p['is_ios']) {
            $query->where(function ($query) {
                $query->where('is_ios', 1)
                    ->orWhere('is_web', 1);
            });
        }
        if ($p['is_android']) {
            $query->where(function ($query) {
                $query->where('is_android', 1)
                    ->orWhere('is_web', 1);
            });
        }
        if ($p['category'] == 0) {
            $query->where('is_recommend', 1);
        } else if ($p['category'] > 0) {
            $query->whereHas('category', function ($query) use ($p) {
                $query->where('l2', $p['category']);
            });
        }

        // 屏蔽指定分类
//        if (env('HIDDEN_GAME_CATEGORY')) {
////            $query->whereDoesntHave('category', function ($query) {
////                $query->where('l1', 1);
////            });
//        }
//        $query->whereDoesntHave('category',function($query){
//            $query->whereIn('l2',[45,77]);
//        });

        if (isset($p['categories']) && count($p['categories']) > 0) {
            $query->whereHas('category', function ($query) use ($p) {
                $query->whereIn('l2', $p['categories']);
            });
        }

        if ($p['my_collect'] == 1) {
            $query->whereHas('collect', function ($query) use ($p) {
                $query->where('user_id', $p['uid'])
                    ->where('status', 2);
            });
        }

        if ($user) {

            // 任务是否完成
            $query->withCount(['download as is_download' => function ($query) use ($p) {
                $query->where('user_id', $p['uid'])
                    ->where('status', 2);
            }]);

            //推荐
            $info = $user->info;
            if (!$info || ($info && $info->ad_status == 1)) {
                $query->where(function ($query) {
                    $query->where('is_download_reward', 0)
                        ->orWhere('download_reward_status', 0);
                });
            }

            // 匹配
            if ($p['is_match']) {
                $userCategory = UserDownloadCategory::where('user_id', $p['uid'])->get();
                $cid = [];
                foreach ($userCategory as $v) {
                    $cid[] = $v['l2'];
                }
                if (count($cid) > 0) {
                    $query->whereHas('category', function ($query) use ($cid) {
                        $query->whereIn('l2', $cid);
                    });
                }
            }
        }

        // 游戏优先
        if ($p['origin'] == 0) {
            $query->whereHas('category', function ($query) {
                $query->where('l1', 1);
            });
        }

        $list = $query
            ->orderBy('reward_amount', 'desc')
            ->orderBy('id','desc')
            ->paginate(20)->toArray();


        foreach ($list['data'] as &$v) {
            $v['origin'] = $p['origin'];
            $v['download_count'] += $v['real_download_count'];
            if (!isset($v['is_download'])) {
                $v['is_download'] = 0;
            }
        }

        return $list;
    }

    public function list($p)
    {
        $list = $this->_list($p);

        // 写搜索词
        if ($p['name']) {
            $word = SearchWord::where('word', $p['name'])->first();
            if (!$word) {
                $word = new SearchWord();
                $word->count = 0;
            }
            $word->count += 1;
            $word->word = $p['name'];
            $word->save();
        }
        // 没有数据
        if ($p['origin'] != 0 && $list['total'] == 0 && $p['page'] == 1 && $p['my_collect'] != 1) {
            $p['category'] = 0;
            $p['name'] = '';
            $p['origin'] = 0;
            return $this->list($p);
        }

        // 只有一条数据
        if ($p['name'] && $list['total'] == 1 && $p['page'] == 1 && $p['my_collect'] != 1) {
            $app = $list['data'][0];
            $category = ProviderAppCategory::where('provider_app_id', $app['id'])->get();
            $categoryId = [];
            foreach ($category as $v) {
                $categoryId[] = $v['l2'];
            }
            $p['categories'] = $categoryId;
            $p['name'] = '';
            $other = $this->_list($p);
            $count = 0;
            foreach ($other['data'] as $v) {
                if ($v['id'] != $app['id']) {
                    $count++;
                    $list['data'][] = $v;
                }
                if ($count == 19) {
                    break;
                }
            }
        }


        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function detail($id, $uid)
    {
        $query = ProviderApp::where('id', $id)
            ->select('id', 'name', 'icon', 'ios', 'web', 'ios_package_name', 'package_name', 'is_download_reward', 'download_reward', 'reward_amount', 'image', 'desc', 'banner', 'download_reward_status', 'provider_id', 'download_count')
            ->with('shop', 'grade')
            ->withCount(['download as real_download_count', 'collect' => function ($query) {
                $query->where('status', 2);
            }])
            ->where('status', APP_STATUS_ABLE);
        if ($uid > 0) {
            $query->withCount(['download as is_download' => function ($query) use ($uid) {
                $query->where('user_id', $uid);
                $query->where('status', 2);
            }]);
            $query->withCount(['collect as is_collect' => function ($query) use ($uid) {
                $query->where(['user_id' => $uid, 'status' => 2]);
            }]);
        }
        if (!$data = $query->first()) {
            return $this->returnData(ERR_PARAM_ERR, '已下架');
        }
        $data = $data->toArray();
        // if ($uid == 0) {
        //     $data['shop'] = [];
        //     $data['ios_package_name'] = '';
        //     $data['package_name'] = '';
        //     $data['web'] = '';
        //     $data['is_download'] = 0;
        //     $data['is_collect'] = 0;
        // }
        $data['download_count'] += $data['real_download_count'];
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function collect($id)
    {
        if (!$app = ProviderApp::where(['id' => $id, 'status' => APP_STATUS_ABLE])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '已下架');
        }
        $user = $this->getUser();
        if (!$collect = UserCollect::where(['provider_app_id' => $id, 'user_id' => $user->id])->first()) {
            $collect = new UserCollect();
            $collect->user_id = $user->id;
            $collect->provider_app_id = $id;
            $collect->status = 1;
        }
        $collect->status = $collect->status == 1 ? 2 : 1;
        $collect->save();
        return $this->returnData();
    }

    public function download($data)
    {
        // 解密
        $secret = '12345qwertabcdef';
        if (!$data = $this->decrypt($data, $secret)) {
            return $this->returnData(ERR_PARAM_ERR, '解密失败');
        }
        $data = json_decode($data, true);
        if (time() - $data['timestamp'] > 5 * 60) {
            return $this->returnData(ERR_PARAM_ERR, '参数错误');
        }
        $user = $this->getUser();
        if ($data['uid'] != $user->id) {
            return $this->returnData(ERR_PARAM_ERR, '参数错误');
        }
        $packageName = $data['package_name'];
        $device = $data['device'];
        $where = [];
        if ($device == 1) {//IOS
            $where['is_ios'] = 1;
            $where['ios_package_name'] = $packageName;
        } else { // Android
            $where['is_android'] = 1;
            $where['package_name'] = $packageName;
        }
        // $userInfo = $user->info;
        try {
            DB::beginTransaction();
            if (!$app = ProviderApp::where($where)->lockForUpdate()->first()) {
                throw  new MyException('参数错误');
            }
            $download = UserDownloadRecord::where(['user_id' => $user->id, 'provider_app_id' => $app->id])->first();
            if ($download) {
                DB::commit();
                return $this->returnData();
            }
            // $number = 'DR' . time() . rand(100000, 999999);
            // $rewardAmount = 0;
            // $userRewardAmount = 0;
            // if ($app->is_download_reward == 1 && $app->reward_amount > 0 && $app->download_reward_status == 1) {
            //     $rewardAmount = $app->reward_amount > $app->download_reward ? $app->download_reward : $app->reward_amount;
            //     if ($userInfo && $userInfo->ad_status = USER_INFO_STATUS_ABLE) {
            //         // 计算用户信息完成度
            //         $completeCount = 4;
            //         if ($userInfo->province != '') {
            //             $completeCount++;
            //         }
            //         if ($userInfo->city != '') {
            //             $completeCount++;
            //         }
            //         if ($userInfo->county != '') {
            //             $completeCount++;
            //         }
            //         if ($userInfo->industry_id != '') {
            //             $completeCount++;
            //         }
            //         if ($userInfo->occupation_id != '') {
            //             $completeCount++;
            //         }
            //         if (count($userInfo->educational_experience) > 0) {
            //             $completeCount++;
            //         }
            //         if (count($userInfo->address) > 0) {
            //             $completeCount++;
            //         }
            //         $userRewardAmount = floor($rewardAmount * ($completeCount / 11));
            //     }
            // }
            // $remind = false;
            // $surplusRewardAmount = $app->reward_amount;
            // $remindAmount = $app->remind_reward;
            // if ($remindAmount > 0 && $surplusRewardAmount >= $remindAmount && $surplusRewardAmount - $rewardAmount < $remindAmount) {
            //     $remind = true;
            // }
            // // 更新佣金
            // $app->reward_amount -= $rewardAmount;
            // $app->save();
            // 写下载记录
            $number = 'DR' . time() . rand(100000, 999999);
            $download = UserDownloadRecord::create([
                'user_id' => $user->id,
                'provider_app_id' => $app->id,
                'number' => $number,
                'reward_amount' => 0,
                'user_reward_amount' => 0,
                'status' => 1,
            ]);
            // // 写用户钱包记录和更新用户钱包
            // if ($userRewardAmount > 0) {
            //     // 更新用户钱包
            //     $wallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->first();
            //     $wallet->balance += $userRewardAmount;
            //     $wallet->save();
            //     // 写用户钱包记录
            //     UserWalletRecord::create([
            //         'user_id' => $user->id,
            //         'order_id' => $download->id,
            //         'number' => $number,
            //         'payment_method' => PAYMENT_METHOD_REWARD,
            //         'amount' => $userRewardAmount,
            //         'type' => USER_WALLET_RECORD_DOWNLOAD_REWARD
            //     ]);
            // }
            // 写用户画像
            $category = $app->category;
            $insert = [];
            foreach ($category as $k => $v) {
                $insert[$k] = [
                    'user_id' => $user->id,
                    'l1' => $v->l1,
                    'l2' => $v->l2,
                    'l3' => $v->l3,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            UserDownloadCategory::insert($insert);
            DB::commit();

            // 发送短信
            // if ($remind) {
            //     (new RemindSrv())->sendRemindProvider($app->provider_id, '推广功能', "{$remindAmount}元");
            // }

            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, '服务器错误');
        }
    }
}

