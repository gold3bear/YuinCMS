<?php

class ManageStatistic extends \BaseController {

    private $group_by;
    private $range_from;
    private $range_to;

    //缓存时间
    const CACHE_MINUTES = 10;

    /**
     *  检查输入的时间范围
     *
     *
     *  @access public
     *  @return void
     */
    public function checkRangeInput() {
        $this->group_by = Input::get('group_by', 'day');

        $this->range_from = Input::get('from', strtotime('-6 day')); //? strtotime($range_from) : strtotime(date('Y-m-d', ));
        $this->range_to   = Input::get('to', strtotime('+1 day')); //$range_to ? strtotime($range_to) : strtotime(date('Y-m-d', strtotime('+1 day')));
        //检查时间输入
        $timeleap         = $this->range_to - $this->range_from;
        if ($timeleap < 0) {
            return [
                'errCode' => 1,
                'msg'     => '选择的时间范围有误'
            ];
        }
        $monthleap = $this->_getMonthLeap($this->range_from, $this->range_to);
        if ($monthleap > 2) {
            return [
                'errCode' => 2,
                'msg'     => '选择的时间范围大于两个月'
            ];
        }
        return '';
    }

    /**
     *  获取pv/ip统计
     *
     *
     *  @access public
     *  @return void
     */
    public function getPv() {
        if ($fail = $this->checkRangeInput()) {
            return $fail;
        }

        $ranges = $this->_fetchRanges();
        $key    = 'stat_pv_' . md5(serialize([$this->range_from, $this->range_to, $this->group_by]));
        $data   = Cache::get($key);
        if (!$data) {
            foreach ($ranges as $pos => $range) {
                $data[$pos]['pv'] = LogRequest::where('parent', 0)
                        ->where('created', '>', $range['begin'])
                        ->where('created', '<', $range['end'])
                        ->count();
                $data[$pos]['ip'] = LogRequest::where('parent', 0)
                        ->where('created', '>', $range['begin'])
                        ->where('created', '<', $range['end'])
                        ->distinct()
                        ->count('ip');
            }
            //指定缓存tag以便清除
            Cache::put($key, $data, self::CACHE_MINUTES);
        }
        //3225
        //2958
        return [
            'ranges'  => array_values($ranges),
            'data'    => array_values($data),
            'errCode' => 0
        ];
    }

    /**
     *  获取api访问统计
     *
     *
     *  @access public
     *  @return void
     */
    function getApi() {
        if ($fail = $this->checkRangeInput()) {
            return $fail;
        }
        $ranges    = $this->_fetchRanges();
        $apps_name = array(
            0 => '默认',
        );

        $key = 'stat_api_' . md5(serialize([$this->range_from, $this->range_to, $this->group_by]));

        if ($data = Cache::get($key)) {
            
        } else {
            foreach ($ranges as $pos => $range) {
                $data[$pos]['request_times'] = [
                    $apps_name[0] => '0',
                ];
                $data[$pos]['downloads']     = [
                    $apps_name[0] => '0',
                ];

                $request_times = LogRequest::select('from_id', DB::raw('count(*) as total'))
                                ->where('parent', 0)
                                ->where('from', 'api')
                                ->where('created', '>', $range['begin'])
                                ->where('created', '<', $range['end'])
                                ->groupBy('from_id')
                                ->get()->toArray();

                $downloads = LogRequest::select('from_id', DB::raw('count(*) as total'))
                                ->where('parent', 0)
                                ->where('from', 'app')
                                ->where('created', '>', $range['begin'])
                                ->where('created', '<', $range['end'])
                                ->groupBy('from_id')
                                ->get()->toArray();

                foreach ($request_times as $row) {
                    $row['from_id']                                           = $row['from_id'] ? $row['from_id'] : 0;
                    $data[$pos]['request_times'][$apps_name[$row['from_id']]] = $row['total'];
                }
                foreach ($downloads as $download) {
                    $download['from_id']                                       = $download['from_id'] ? $download['from_id'] : 0;
                    $data[$pos]['downloads'][$apps_name[$download['from_id']]] = $download['total'];
                }
            }
            Cache::put($key, $data, self::CACHE_MINUTES);
        }
        //17s
        return [
            'ranges'  => array_values($ranges),
            'data'    => array_values($data),
            'errCode' => 0
        ];
    }

    /**
     *  获取平台下载统计
     *
     *
     *  @access public
     *  @return void
     */
    function getPlatform() {
        if ($fail = $this->checkRangeInput()) {
            return $fail;
        }
        $ranges = $this->_fetchRanges();

        //应用下载的总次数
//        $app_total_downs = Appinfo::select('hits', DB::raw('sum(`hits`)'))
//                        ->get()->toArray();
        //按平台分,应用下载的总次数
//        $downs_by_platform = Appinfo::select(`platform`, DB::raw('sum(`hits`) as total'))
//                        ->groupBy('platform')
//                        ->get()->toArray();
        //遍历分组
        //时间段分别查询
        $key = 'stat_platform_' . md5(serialize([$this->range_from, $this->range_to, $this->group_by]));

        if ($data = Cache::get($key)) {
            
        } else {
            $appinfo_ids = LogRequest::select('resource_id')
                    ->where('resource_type', 'application_download')
                    ->where('created', '>', $this->range_from)
                    ->where('created', '<', $this->range_to)
                    ->lists('resource_id');
            $platforms   = explode('|', Option::get('application::platform'));

            foreach ($ranges as $pos => $range) {
                $data[$pos] = ['total' => 0];
                foreach ($platforms as $p) {
                    $data[$pos][$p] = 0;
                }
                $temp = LogRequest::select('resource_id', DB::raw('COUNT( * ) as downs'))
                                ->where('resource_type', 'application_download')
                                ->where('created', '>', $range['begin'])
                                ->where('created', '<', $range['end'])
                                ->groupBy('resource_id')
                                ->get()->toArray();
                foreach ($temp as $row) {
                    $p              = isset($platforms[$row['resource_id']]) ? $platforms[$row['resource_id']] : '未知';
                    $data[$pos][$p] = isset($ranges[$pos][$p]) ? $ranges[$pos][$p] : 0;
                    $data[$pos][$p] += (int) $row['downs'];
                    $data[$pos]['total'] += (int) $row['downs'];
                }
            }
            Cache::put($key, $data, self::CACHE_MINUTES);
        }
        return [
            'ranges'  => array_values($ranges),
            'data'    => array_values($data),
            'errCode' => 0
        ];
    }

    /**
     *  获取分类下载统计
     *
     *
     *  @access public
     *  @return void
     */
    function getAppdown() {
        if ($fail = $this->checkRangeInput()) {
            return $fail;
        }
        $ranges = $this->_fetchRanges();

        $key = 'stat_appdown_' . md5(serialize([$this->range_from, $this->range_to, $this->group_by]));

        if ($data = Cache::get($key)) {
            
        } else {
            $categories = Category::where('type', 'application')->get();
            //初始化分类计数数组
            $result     = [];
            $temp       = $categories->modelKeys();
            $temp2      = array_keys($ranges);
            foreach ($temp as $v) {
                foreach ($temp2 as $v2) {
                    $result[$v][$v2] = 0;
                }
            }

            $request_data = LogRequest::where('resource_type', 'category_application')
                            ->where('request_type', 'download')
                            ->where('created', '>', $this->range_from)
                            ->where('created', '<', $this->range_to)
                            ->get()->toArray();
            foreach ($ranges as $pos => $range) {
                foreach ($request_data as $r) {
                    if ($range['begin'] <= $r['created'] && $r['created'] <= $range['end']) {
                        $result [$r['resource_id']][$pos] ++;
                    }
                }
            }

            foreach ($result as $k => $r) {
                if ($a = $categories->find($k)) {
                    $a->data = array_values($r);
                }
            }
            $data = $categories->toArray();
            Cache::put($key, $data, self::CACHE_MINUTES);
        }
        return [
            'ranges'  => array_values($ranges),
            'data'    => array_values($data),
            'errCode' => 0
        ];
    }

    /**
     *  获取营业厅下载统计
     *
     *
     *  @access public
     *  @return void
     */
    function getHalls() {
        if ($fail = $this->checkRangeInput()) {
            return $fail;
        }
        $ranges = $this->_fetchRanges();


        $city_counts    = array();
        $halls_requests = array();

        //取出这段时间内所有来自营业厅的请求
        $requests = LogRequest::where('from', 'hall')
                        ->where('created', '>', $this->range_from)
                        ->where('created', '<', $this->range_to)
                        ->orderBy('created', 'asc')
                        ->get()->toArray();

        //按照营业厅id分组
        foreach ($requests as $request) {
            if (!isset($halls_requests[$request['from_id']])) {
                $halls_requests[$request['from_id']] = array();
            }
            $halls_requests[$request['from_id']][] = $request;
        }

        //用id数组遍历并计算各自的ip/pv
        foreach ($this->halls as $hall_id => $hall) {
            $hall_requests = isset($halls_requests[$hall_id]) ? $halls_requests[$hall_id] : array();
            $keys          = array_keys($ranges);
            $index         = 0;
            $key           = $keys[$index];
            $ips           = array();
            foreach ($hall_requests as $request) {
                foreach ($ranges as $pos => $range) {
                    if ($range['begin'] <= $request['created'] && $request['created'] <= $range['end']) {
                        if (!isset($ips[$request['ip']])) {
                            $ips[$request['ip']] = true;
                            $ranges[$pos]['ip']  = isset($ranges[$pos]['ip']) ? $ranges[$pos]['ip'] + 1 : 0;
                        }
                        if (!isset($ranges[$pos][$request['request_method']])) {
                            $ranges[$pos][$request['request_method']] = 0;
                        }
                        if ($request['resource_type'] == 'application') {
                            $ranges[$pos][$request['request_method']] = isset($ranges[$pos][$request['request_method']]) ? $ranges[$pos][$request['request_method']] + 1 : 0;
                        }
                        if ($request['request_type'] == 'view' && $request['parent'] == 0) {
                            $ranges[$pos]['pv'] = isset($ranges[$pos]['pv']) ? $ranges[$pos]['pv'] + 1 : 0;
                        }
                    }
                }
            }
            $this->halls[$hall_id]['result'] = $ranges;
            if (!isset($city_counts[$this->halls[$hall_id]['city_id']])) {
                //total : PV, halls => 营业厅输了, QR:通过二维码下载, sms_share :短信分享次数, appwall_window => '应用查看次数'
                $city_counts[$this->halls[$hall_id]['city_id']] = array(
                    'total'          => 0, 'halls'          => 0, 'QR'             => 0, 'sms_share'      => 0, 'appwall_window' => 0
                );
            }
            $city_counts[$this->halls[$hall_id]['city_id']]['halls'] ++;
            foreach ($this->halls[$hall_id]['result'] as $r) {
                if (isset($r['pv'])) {
                    $city_counts[$this->halls[$hall_id]['city_id']]['total'] += $r['pv'];
                } else {
                    $city_counts[$this->halls[$hall_id]['city_id']]['total'] = 0;
                }
                foreach (array('QR', 'sms_share', 'appwall_window') as $key) {
                    if (isset($r[$key])) {
                        $city_counts[$this->halls[$hall_id]['city_id']][$key] += $r[$key];
                    } else {
                        $city_counts[$this->halls[$hall_id]['city_id']][$key] = 0;
                    }
                }
            }
        }
        if (Input::get('excel')) {
            return $this->_AWHallExcel($this->halls, $city_counts);
        }
        return ['halls' => $this->halls, 'data' => $city_counts];
    }

    /**
     *  获取文章绩效统计
     *
     *
     *  @access public
     *  @return void
     */
    function getQuality() {
        if ($fail = $this->checkRangeInput()) {
            return $fail;
        }
        //设定对于文章,统计的有效天数
        $days = 3;

        $subjects = Article::where('created', '>', $this->range_from)
                ->where('created', '<', $this->range_to)
                ->where('status', 1);
        if ($uid      = Input::get('uid')) {
            $subjects->where('user_id', $uid);
        }
        $subjects = $subjects->orderBy('created', 'desc')->get();

        //这里用一下orm的存在关系
        $todo_article = Article::where('created', '>', $this->range_from)
                ->where('created', '<', time() - $days * 24 * 3600)
                ->where('status', 1)
                ->has('quality', '=', 0)
                ->get();

        //做增量更新
        //取最后一次更新的时间
        $l1_v = (int) Option::get('subject_quality::lv1_views');
        $l2_v = (int) Option::get('subject_quality::lv2_views');
        $l1_c = (int) Option::get('subject_quality::lv1_comments');

        foreach ($todo_article as $subject) {
            $last_time           = $subject['created'] + $days * 24 * 3600;
            $quality             = new SubjectQuality;
            $quality->subject_id = $subject['id'];
//            $quality->views = LogRequest::select()
//                    ->where('resource_type = ? AND resource_id = ? AND created < ?', 'subject', $subject['id'], $last_time)
//                    ->count();
            $quality->views      = LogRequest::where('resource_type', 'subject')
                    ->where('resource_id', $subject['id'])
                    ->where('created', '<', $last_time)
                    ->count();

            if ($quality->views == 0) {
                $quality->views = $subject['views'];
            }
            try {
                $quality->comments = DB::table('duoshuo_comments')->select()
                        ->where('belong', $subject['id'])
                        ->where('status', '>', 0)
                        ->where('created', '<', $last_time)
                        ->count();
            } catch (Exception $e) {
                $quality->comments = $subject['comments'];
            }
            if ($quality->views >= $l2_v && $quality->comments >= $l1_c) {
                $quality->quality = 3;
            } elseif ($quality->views >= $l1_v) {
                $quality->quality = 2;
            } else {
                $quality->quality = 1;
            }
            $quality->created = time();
            $quality->save();
        }
        $a_quality = array();
        foreach ($subjects as $subject) {
            if ((time() - $subject->created) >= $days * 24 * 3600) {
                $o_quality = SubjectQuality::where('subject_id', $subject->id)->take(1)->get()->first();
                if ($o_quality) {
                    $a_quality[$subject->id]['views']    = $o_quality->views;
                    $a_quality[$subject->id]['comments'] = $o_quality->comments;
                    $a_quality[$subject->id]['quality']  = $o_quality->quality;
                }

//                $this->provider->cacher->set('stat_subject_info_' . $subject->id, $a_quality[$subject->id], (time() - $subject->created));
            }
        }
        if (Input::has('excel')) {
            return $this->_QualityCsv($subjects, $a_quality);
        }

        return $a_quality;
    }

    /**
     *  输出文章绩效到csv
     *
     *
     *  @access public
     *  @return void
     */
    function _QualityCsv($subjects, $a_quality) {
        $str = "ID, 标题, 发布日期, 分类, 作者, 三天阅读数, 三天评论数, 评分";
        $str .= "\n";
        foreach ($subjects as $subject) {
            $id       = $subject->id;
            $title    = str_replace(',', '，', $subject->title);
            $date     = date('Y-m-d H:i:s', $subject->created);
            $categy   = implode('，', $subject->categories->values('name'));
            $author   = $subject->username;
            $view     = $a_quality[$subject->id]['views'];
            $comments = $a_quality[$subject->id]['comments'];
            $quality  = $a_quality[$subject->id]['quality'];

            $str .= join(',', array($id, $title, $date, $categy, $author, $view, $comments, $quality)) . "\n"; //用引文逗号分开
        }
        $filename = 'export.csv'; //设置文件名

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
        die;
    }

    /**
     *  获取月份差
     *
     *
     *  @access public
     *  @return void
     */
    function _getMonthLeap($st, $et) {
        $s_m   = date('n', $st);
        $e_m   = date('n', $et);
        $s_y   = date('Y', $st);
        $e_y   = date('Y', $et);
        $total = 13 - $s_m + ($e_y - $s_y - 1) * 12 + $e_m; //计算月份差
        return $total;
    }

    /**
     *  根据每日,每周或每月分割时间范围
     *
     *
     *  @access public
     *  @return void
     */
    function _fetchRanges() {
        $result = array();
        $end    = 0;
        $ymdhis = date('Y-m-d', $this->range_from);
        list( $y, $m, $d) = explode('-', $ymdhis);

        while ($end < $this->range_to) {

            switch ($this->group_by) {
                case 'day' :
                    $current = mktime(0, 0, 0, $m, $d++, $y);
                    $end     = mktime(0, 0, 0, $m, $d, $y);

                    break;
                case 'weekly' :
                    $end     = !$end ? mktime(0, 0, 0, $m, $d, $y) : $end;
                    $current = $end;
                    $end     = strtotime('next monday', $current);
                    break;
                case 'month' :
                    $current = mktime(0, 0, 0, $m++, 1, $y);
                    $end     = mktime(0, 0, 0, $m, 1, $y);

                    break;
            }
            $end              = $end > $this->range_to ? $this->range_to : $end;
            $result[$current] = array(
                'begin' => $current, 'end'   => $end
            );
        }
        return $result;
    }

}
