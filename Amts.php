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
 * @inheritdoc 使用说明
 * 先设置参数以下
 *  \aliyuns\Amts::$mps_region_id = ''
 *  \aliyuns\Amts::$access_key_id = ''
 *  \aliyuns\Amts::$access_key_secret = ''
 *  \aliyuns\Amts::$template_id = ''
 *  \aliyuns\Amts::$oss_location = ''
 *  \aliyuns\Amts::$oss_bucket = ''
 *  \aliyuns\Amts::$oss_input_object = ''
 *  \aliyuns\Amts::$oss_output_object = ''
 *  \aliyuns\Amts::init();  // 执行提交作业进行  - 转码任务
 */
class Amts
{
    /**
     * 所属地区 默认：深圳
     * @var string
     */
    static public $mps_region_id = 'cn-shenzhen';
    /**
     * 阿里云access_key_id
     * @var string
     */
    static public $access_key_id = '';
    /**
     * 阿里云access_key_secret
     * @var string
     */
    static public $access_key_secret = '';
    /**
     * 模板ID 可选填
     * @defualt : S00000001-200010
     * @var string
     */
    static public $template_id = 'S00000001-200010';
    /**
     *  所属地区管道pipeline_id
     * @var string
     */
    static public $pipeline_id = '';
    /**
     * 所属地区oss站点
     * @var string
     */
    static public $oss_location = 'oss-cn-shenzhen';
    /**
     * 阿里云OSS bucket名称
     * @var string
     */
    static public $oss_bucket = '';
    /**
     * 阿里云OSS 需要转码视频文件地址
     * @var string
     * @type oos-filePath
     */
    static public $oss_input_object = '';
    /**
     * 阿里云OSS 需要 ·输出· 转码视频文件地址
     * @var string
     * @type oos-filePath
     */
    static public $oss_output_object='';
    /**
     * 转码输出对象初始化
     * @var array
     */
    static private $outputs = [];
    /**
     * DefaultAcsClient实例并初始化对象
     * @object \DefaultAcsClient
     */
    static private $client = [];
    /**
     * 创建API请求对象
     * @object  \Mts\SubmitJobsRequest
     */
    static private $request = [];

    public static function init()
    {
        self::initParam();
        self::getPipelineId();
        self::initClient();
        self::initOutput();
        self::initRequest();
        self::exec(self::$client, self::$request);
    }

    /**
     * 检验参数，进行污水处理
     */
    public static function initParam()
    {
        self::$mps_region_id == '' && self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'mps_region_id is null']);
        self::$access_key_id == ''&&self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'access_key_id is null']);
        self::$access_key_secret == ''&&self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'access_key_secret is null']);
        self::$template_id == ''&&self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'template_id is null']);
        self::$oss_location == ''&&self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'oss_location is null']);
        self::$oss_bucket == ''&&self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'oss_bucket is null']);
        self::$oss_input_object == ''&&self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'oss_input_object is null']);
        self::$oss_output_object == ''&&self::msg(400,'BAD REQUEST',['Error: ' => '参数错误' ,' Message' => 'ss_output_object is null']);
    }

    /**
     * 获取管道ID
     * 获取pipeline_id
     * @return mixed
     */
    public static function getPipelineId()
    {
        session('pipeline_id') != ''&& self::$pipeline_id =session('pipeline_id');
        if (self::$pipeline_id == ''){
            $iClientProfile = \DefaultProfile::getProfile(self::$mps_region_id, self::$access_key_id, self::$access_key_secret);
            $client = new \DefaultAcsClient($iClientProfile);
            $request = new Mts\SearchPipelineRequest();
            $response = $client->getAcsResponse($request);
            $pipelines = json_decode(json_encode($response->PipelineList->Pipeline), true);
            session_start();
            session('pipeline_id',$pipelines[0]['Id']);
            self::$pipeline_id = $pipelines[0]['Id'];
        }
    }


    /**
     * 创建DefaultAcsClient实例并初始化
     * @self::$client \DefaultAcsClient
     */
    public static function initClient()
    {
        # 创建DefaultAcsClient实例并初始化
        $clientProfile = \DefaultProfile::getProfile(
            self::$mps_region_id,                   # 您的 Region ID
            self::$access_key_id,                   # 您的 AccessKey ID
            self::$access_key_secret                # 您的 AccessKey Secret
        );
        self::$client = new \DefaultAcsClient($clientProfile);
    }

    /**
     * 创建API请求并设置参数
     * @self::$request Mts\SubmitJobsRequest
     */
    public static function initRequest()
    {
        # 创建API请求并设置参数
        $request = new Mts\SubmitJobsRequest();
        $request->setAcceptFormat('JSON');
        # Input
        $input = array('Location' => self::$oss_location,
            'Bucket' => self::$oss_bucket,
            'Object' => urlencode(self::$oss_input_object));
        $request->setInput(json_encode($input));
        $request->setOUtputs(json_encode(self::$outputs));
        $request->setOutputBucket(self::$oss_bucket);
        $request->setOutputLocation(self::$oss_location);
        # PipelineId
        $request->setPipelineId(self::$pipeline_id);
        self::$request = $request;
    }

    /**
     * 初始化输出资源类型
     * @return array
     */
    public static function initOutput()
    {
        # Output
        $output = array('OutputObject' => urlencode(self::$oss_output_object));
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
        $output['TemplateId'] = self::$template_id;
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