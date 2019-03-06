<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Util_EweiShopV2Model
{
    public function getExpressList($express, $expresssn)
    {
        $url = 'http://wap.kuaidi100.com/wap_result.jsp?rand=' . time() . '&id=' . $express . '&fromWeb=null&postid=' . $expresssn;
        load()->func('communication');
        $resp = ihttp_request($url);
        $content = $resp['content'];

        if (empty($content)) {
            return array();
        }


        preg_match_all('/\\<p\\>&middot;(.*)\\<\\/p\\>/U', $content, $arr);

        if (!isset($arr[1])) {
            return false;
        }


        $arr = $arr[1];
        $list = array();

        if ($arr) {
            $len = count($arr);
            $step1 = explode('<br />', str_replace('&middot;', '', $arr[0]));
            $step2 = explode('<br />', str_replace('&middot;', '', $arr[$len - 1]));
            $i = 0;

            while ($i < $len) {
                if (strtotime(trim($step2[0])) < strtotime(trim($step1[0]))) {
                    $row = $arr[$i];
                } else {
                    $row = $arr[$len - $i - 1];
                }

                $step = explode('<br />', str_replace('&middot;', '', $row));
                $list[] = array('time' => trim($step[0]), 'step' => trim($step[1]), 'ts' => strtotime(trim($step[0])));
                ++$i;
            }
        }


        return $list;
    }

    public function getIpAddress()
    {
        $ipContent = file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js');
        $jsonData = explode('=', $ipContent);
        $jsonAddress = substr($jsonData[1], 0, -1);
        return $jsonAddress;
    }

    public function checkRemoteFileExists($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $result = curl_exec($curl);
        $found = false;

        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $found = true;
            }

        }


        curl_close($curl);
        return $found;
    }

    /**
     * 计算两组经纬度坐标 之间的距离
     * params ：lat1 纬度1； lng1 经度1； lat2 纬度2； lng2 经度2； len_type （1:m or 2:km);
     * return m or km
     */
    public function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
    {
        $pi = 3.1415926000000001;
        $er = 6378.1369999999997;
        $radLat1 = ($lat1 * $pi) / 180;
        $radLat2 = ($lat2 * $pi) / 180;
        $a = $radLat1 - $radLat2;
        $b = (($lng1 * $pi) / 180) - (($lng2 * $pi) / 180);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + (cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))));
        $s = $s * $er;
        $s = round($s * 1000);

        if (1 < $len_type) {
            $s /= 1000;
        }

        return round($s, $decimal);
    }

    public function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
    {
        if (is_array($multi_array)) {
            foreach ($multi_array as $row_array) {
                if (is_array($row_array)) {
                    $key_array[] = $row_array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }

        array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }
}


?>