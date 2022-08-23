<?php


namespace App\Srv\Utils;


use AlibabaCloud\SDK\Sts\V20150401\Models\AssumeRoleRequest;
use AlibabaCloud\SDK\Sts\V20150401\Sts;
use App\Srv\Srv;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use OSS\OssClient;

class UploadSrv extends Srv
{
    public function sts()
    {
        try {
            $config = new Config([
                "accessKeyId" => env('OSS_STS_KEY'),
                "accessKeySecret" => env('OSS_STS_SECRET'),
            ]);
            $config->endpoint = env('OSS_STS_OSS_ENDPOINT');
            $client = new Sts($config);
            $assumeRoleRequest = new AssumeRoleRequest([
                "roleArn" => env('OSS_STS_ROLE_ARN'),
                "roleSessionName" => env('OSS_STS_ROLE_SESSION_NAME')
            ]);
            $data = $client->assumeRole($assumeRoleRequest)->toMap();
            return $this->returnData(ERR_SUCCESS, '', $data['body']['Credentials']);
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function ossClient()
    {
        return new OssClient(env('OSS_KEY'), env('OSS_SECRET'), env('OSS_ENDPOINT'));
    }

    public function uploadImage(UploadedFile $file)
    {
        if (!$file->isValid()) {
            return $this->returnData(ERR_PARAM_ERR, '无效的图片');
        }
        $size = $file->getSize();
        if ($size > 5 * 1024 * 1024) {
            return $this->returnData(ERR_PARAM_ERR, '图片大小不能超过5M');
        }
        $mimeType = $file->getMimeType();
        if (stripos($mimeType, 'image') === false) {
            return $this->returnData(ERR_PARAM_ERR, '图片格式错误');
        }
        $ext = $file->extension();
        $name = md5($file->getRealPath() . time() . $file->getFilename() . rand(10000, 9999999));
        $object = "{$name}.{$ext}";
        $endpoint = env('OSS_ENDPOINT');
        $bucket = env('OSS_BUCKET');
        try {
            $this->ossClient()->putObject($bucket, $object, $file->getContent());
            return $this->returnData(ERR_SUCCESS, '', "https://{$bucket}.{$endpoint}/{$object}");
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '上传失败');
        }
    }

    public function uploadFile(UploadedFile $file)
    {
        if (!$file->isValid()) {
            return $this->returnData(ERR_PARAM_ERR, '无效的文件');
        }
        $object = $file->getClientOriginalName();
        $endpoint = env('OSS_ENDPOINT');
        $bucket = env('OSS_BUCKET');
        try {
            $this->ossClient()->putObject($bucket, $object, $file->getContent());
            return $this->returnData(ERR_SUCCESS, '', "https://{$bucket}.{$endpoint}/{$object}");
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '上传失败');
        }
    }
}
