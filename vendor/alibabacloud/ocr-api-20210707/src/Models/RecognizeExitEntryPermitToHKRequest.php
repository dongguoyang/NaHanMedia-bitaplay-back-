<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Ocrapi\V20210707\Models;

use AlibabaCloud\Tea\Model;
use GuzzleHttp\Psr7\Stream;

class RecognizeExitEntryPermitToHKRequest extends Model
{
    /**
     * @description 图案坐标信息输出，针对结构化，如身份证人脸头像
     *
     * @var bool
     */
    public $outputFigure;

    /**
     * @description 图片链接（长度不超 2048，不支持 base64）
     *
     * @var string
     */
    public $url;

    /**
     * @description 图片二进制字节流，最大10MB
     *
     * @var Stream
     */
    public $body;
    protected $_name = [
        'outputFigure' => 'OutputFigure',
        'url'          => 'Url',
        'body'         => 'body',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->outputFigure) {
            $res['OutputFigure'] = $this->outputFigure;
        }
        if (null !== $this->url) {
            $res['Url'] = $this->url;
        }
        if (null !== $this->body) {
            $res['body'] = $this->body;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return RecognizeExitEntryPermitToHKRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['OutputFigure'])) {
            $model->outputFigure = $map['OutputFigure'];
        }
        if (isset($map['Url'])) {
            $model->url = $map['Url'];
        }
        if (isset($map['body'])) {
            $model->body = $map['body'];
        }

        return $model;
    }
}
