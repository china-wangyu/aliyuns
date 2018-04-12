<?php
/**
 * Created by PhpStorm.
 * User: china_wangyu@aliyun.com
 * Date: 2018/4/12
 * Time: 11:14
 */

namespace aliyuns;

include_once 'aliyun-php-sdk-core/Config.php';

use Mts\Request\V20140618 as Mts;

/**
 * 阿里云转码类
 * Class AliyunMts
 * @package Aliyun 阿里云SDK库
 * @author china_wangyu@aliyun.com
 */
class Amts
{


    public static function init(string $inputObj = 'video/1.mp4',string $outputObj = 'output2.mp4')
    {
        $params = self::initParams($inputObj,$outputObj);
        $client = self::initClient($params['mps_region_id'], $params['access_key_id'], $params['access_key_secret']);
        $outputs = self::initOutput($params['oss_output_object'], $params['template_id']);
        $request = self::initRequest($params, $outputs);
        self::exec($client, $request);
    }

    /**
     * 获取pipeline_id
     * @param $mps_region_id
     * @param $access_key_id
     * @param $access_key_secret
     * @return mixed
     */
    public static function getPipelineId($mps_region_id, $access_key_id, $access_key_secret)
    {
        $iClientProfile = \DefaultProfile::getProfile($mps_region_id, $access_key_id, $access_key_secret);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new Mts\SearchPipelineRequest();
        $response = $client->getAcsResponse($request);
        $pipelines = json_decode(json_encode($response->PipelineList->Pipeline), true);
//        echo '<pre>';
//        echo $pipelines[0]['Id'];
        return $pipelines[0]['Id'];
    }


    /**
     * 初始化请求资源参数
     * @return mixed
     */
    public static function initParams(string $inputObj,string $outputObj)
    {
        $param['mps_region_id'] = 'cn-shenzhen';
        $param['access_key_id'] = 'LTAIl04lLlhmw38Q';
        $param['access_key_secret'] = 'KDvJMzVyR3tQoPogaDcYvhokZXbyi1';
        $param['pipeline_id'] = self::getPipelineId($param['mps_region_id'], $param['access_key_id'], $param['access_key_secret']);
        $param['template_id'] = 'S00000001-200010';
        $param['oss_location'] = 'oss-cn-shenzhen';
        $param['oss_bucket'] = 'tiaoba';
        $param['oss_input_object'] = $inputObj;
        $param['oss_output_object'] = $outputObj;
        return $param;
    }

    /**
     * 创建DefaultAcsClient实例并初始化
     * @param $mps_region_id
     * @param $access_key_id
     * @param $access_key_secret
     * @return \DefaultAcsClient
     */
    public static function initClient($mps_region_id, $access_key_id, $access_key_secret)
    {
        # 创建DefaultAcsClient实例并初始化
        $clientProfile = \DefaultProfile::getProfile(
            $mps_region_id,                   # 您的 Region ID
            $access_key_id,                   # 您的 AccessKey ID
            $access_key_secret                # 您的 AccessKey Secret
        );
        $client = new \DefaultAcsClient($clientProfile);
        return $client;
    }

    /**
     * 创建API请求并设置参数
     * @param $param
     * @return Mts\SubmitJobsRequest
     */
    public static function initRequest($param, $outputs)
    {
        # 创建API请求并设置参数
        $request = new Mts\SubmitJobsRequest();
        $request->setAcceptFormat('JSON');
        # Input
        $input = array('Location' => $param['oss_location'],
            'Bucket' => $param['oss_bucket'],
            'Object' => urlencode($param['oss_input_object']));
        $request->setInput(json_encode($input));
        $request->setOUtputs(json_encode($outputs));
        $request->setOutputBucket($param['oss_bucket']);
        $request->setOutputLocation($param['oss_location']);
        # PipelineId
        $request->setPipelineId($param['pipeline_id']);
        return $request;
    }

    /**
     * 初始化输出资源类型
     * @param $oss_output_object
     * @param $template_id
     * @return array
     */
    public static function initOutput($oss_output_object, $template_id)
    {
        # Output
        $output = array('OutputObject' => urlencode($oss_output_object));
        # Ouput->Container
        $output['Container'] = array('Format' => 'mp4');
        # Ouput->Video
        $output['Video'] = array('Codec' => 'H.264',
            'Bitrate' => 1500,
            'Width' => 1280,
            'Fps' => 25);
        # Ouput->Audio
        $output['Audio'] = array('Codec' => 'AAC',
            'Bitrate' => 128,
            'Channels' => 2,
            'Samplerate' => 44100);
        # Ouput->TemplateId
        $output['TemplateId'] = $template_id;
        $outputs = array($output);
        return $outputs;
    }

    /**
     * 执行视频转码
     * @param \DefaultAcsClient $client
     * @param Mts\SubmitJobsRequest $request
     */
    public static function exec(\DefaultAcsClient $client, Mts\SubmitJobsRequest $request)
    {
        # 发起请求并处理返回
        try {
            $response = $client->getAcsResponse($request);
            if ($response->{'JobResultList'}->{'JobResult'}[0]->{'Success'}) {
                self::msg(200,'Success',['RequestId'=>$response->{'RequestId'},'JobId'=>$response->{'JobResultList'}->{'JobResult'}[0]->{'Job'}->{'JobId'}]);
            } else {
                self::msg(200,'Success','');
            }
        } catch (\ServerException $e) {
            self::msg(400,'BAD REQUEST',['Error: ' => $e->getErrorCode() ,' Message' => $e->getMessage()]);
        } catch (\ClientException $e) {
            self::msg(400,'BAD REQUEST',['Error: ' => $e->getErrorCode() ,' Message' => $e->getMessage()]);
        }
    }


    /**
     * 统一返回格式
     * @param $code
     * @param $msg
     * @param array $data
     */
    static function msg($code,$msg,array $data=[]){
        $data =  [
            'returnCode'=>$code,
            'returnMsg'=>$msg,
            'data'=>$data,
        ];
        header("HTTP/1.1 " . $code . " " . $msg);
        header('Content-Type:application/json;charset=utf-8');
        if ($data !== null) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
}