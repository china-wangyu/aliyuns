# Aliyuns

- 阿里云媒体处理，转码，搜索管道，提交作业等

## Requirements

- PHP 5.3+

## Example

```php
        /**
         * 初始化参数
         * \aliyuns\Amts::$mps_region_id 默认：cn-shenzhen
         * \aliyuns\Amts::$oss_location 默认：oss-cn-shenzhen
         * \aliyuns\Amts::$access_key_id 阿里云access_key_id需要有对应权限
         */
        \aliyuns\Amts::$access_key_id = '阿里云access_key_id';
        \aliyuns\Amts::$access_key_secret = '阿里云access_key_secret';
        \aliyuns\Amts::$oss_bucket = '阿里云 oss_bucket';
        
        // oss 所属区域
        \aliyuns\Amts::$mps_region_id = 'cn-shenzhen';
        \aliyuns\Amts::$oss_location = 'oss-'.\aliyuns\Amts::$mps_region_id;
        
        \aliyuns\Amts::$template_id = 'S00000001-200010';
        
        // oss 视频源-地址
        \aliyuns\Amts::$oss_input_object = '1.mp4';
        // oss 转码视频存放地址
        \aliyuns\Amts::$oss_output_object = 'output3.mp4';
        
        // 获取管道ID，提交作业，视频转码
        \aliyuns\Amts::init();
```

## Auther 

china_wangyu@aliyun.com