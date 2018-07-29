<?php

/**
 * 图片合并、文字合并
 * Class ImageMerge
 * @author  sorbon      <1209sorbon@gmail.com>
 * @version 1.0.0
 */
class ImageMerge
{
    /**
     * 临时图片文件夹
     * @tips: 临时图片创建后删除
     * @var string $tmp_dir
     */
    private static $tmp_dir = 'static/mini/tmp_image/';

    /**
     * 图片文件夹
     * @var string $image_dir
     */
    private static $image_dir = 'static/mini/image/';

    /**
     * 头像图 宽度
     * @var int $head_width
     */
    private $head_width = 217;

    /**
     * 头像图 高度
     * @var int $head_high
     */
    private $head_high = 217;

    /**
     * 主图
     * @var string $master_image
     */
    public $master_image;

    /**
     * 辅图
     * @var string $auxiliary_image
     */
    public $auxiliary_image;

    /**
     * 初始化
     * ImageMerge constructor.
     * @param $master_image
     * @param string $target_url
     */
    public function __construct (string $master_image, string $target_url = '')
    {
        $this->master_image    = $master_image;
        $this->auxiliary_image = $target_url;
    }

    /**
     * 合并图片文字
     * @tips: 需求为 添加多个文本，所以config 为 索引数组
     * @param array $config
     * @param null $new_img_path
     * @return array
     */
    public function mergeImageText (array $config, $new_img_path = null) : array
    {
        try {
            if ( ! $image = $this->getImgType($this->master_image)) return ['status' => 0];

            foreach ($config as $value) {
                $text             = $value['text'];                 // 文字
                $angle            = $value['angle'];                // 角度
                $font_size        = $value['font_size'];            // 字体大小
                $font_url         = $value['font_url'];             // 字体地址
                $color            = $value['font_color'];           // 字体颜色 rgb
                $font_coordinates = $value['font_coordinates'];     // 字体坐标

                ImageTTFBBox($font_size, $angle, $font_url, $text);

                // 字体颜色
                $font_color = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);

                imageTTFText($image,
                    $font_size,
                    $angle,
                    $font_coordinates['x'],
                    $font_coordinates['y'],
                    $font_color,
                    $font_url, $text);
            }

            $image_path = $new_img_path ?? $this->master_image;

            imagepng($image, $image_path);

            imagedestroy($image);

            return ['status' => 1, 'data' => $image_path];
        } catch (\Exception $e) {
            return ['status' => 0, 'info' => $e->getMessage()];
        }
    }

    /**
     * 图片合并
     * @return array
     */
    public function mergeImage () : array
    {
        try {
            if ( ! $background = $this->getImgType($this->master_image)) return ['status' => 0];

            if ( ! $target = $this->getImgType($this->auxiliary_image)) return ['status' => 0];

            imagesavealpha($background, true); // 防止图片失帧
            $im = imagecreatetruecolor(imagesx($background), imagesy($background));

            imagecopy($im,
                $background,
                0,
                0,
                0,
                0,
                imagesx($background),
                imagesy($background));

            imagecopyresampled($im,
                $target,
                205 * 2,        // 2倍图 坐标需要乘以2
                456 * 2,
                0,
                0,
                intval(imagesx($target)),
                intval(imagesy($target)),
                imagesx($target),
                imagesy($target));

            $filename = self::$image_dir . md5(rand(1, 10000) . time()) . ".png";

            imagepng($im, $filename);

            @unlink($this->auxiliary_image);    // 删除合成头像二维码图片

            return ['status' => 1, 'data' => $filename];
        } catch (\Exception $e) {
            return ['status' => 0, 'info' => $e->getMessage()];
        }
    }

    /**
     * 改变二维码中间logo
     * @tips: 更换为微信头像
     * @param string $avatarUrl 微信头像地址
     * @return array
     */
    public function changeImageLogo ($avatarUrl) : array
    {
        try {
            $img_file      = file_get_contents($avatarUrl);
            $img_content   = base64_encode($img_file);
            $file_tou_name = time() . mt_rand(9999, 9999) . ".png";
            $head_url      = self::$tmp_dir . $file_tou_name;

            file_put_contents($head_url, base64_decode($img_content));

            $roundness_image = $this->roundnessImage($head_url);                       // 原图资源
            $image           = $this->getImgType($head_url);
            $target_im       = imagecreatetruecolor(imagesx($image), imagesy($image)); // 创建一个新的画布（缩放后的），从左上角开始填充透明背景

            imagesavealpha($target_im, true);

            $trans_colour = imagecolorallocatealpha($target_im, 0, 0, 0, 127);

            imagefill($target_im, 0, 0, $trans_colour);

            imagecopyresampled($target_im,
                $roundness_image,
                0,
                0,
                0,
                0,
                imagesx($roundness_image),
                imagesy($roundness_image),
                imagesx($image),
                imagesy($image));

            $file_head_name = "23" . time() . ".png";
            $comp_path      = self::$tmp_dir . $file_head_name;

            imagepng($target_im, $comp_path);
            imagedestroy($target_im);

            @unlink($head_url);                 // 删除临时保存的微信头像

            //传入保存后的二维码地址
            return $this->create_pic_watermark($this->auxiliary_image, $comp_path);
        } catch (\Exception $e) {
            return ['status' => 0, 'info' => $e->getMessage()];
        }
    }

    /**
     * 剪切头像为圆形
     * @param  string $img_path 头像保存之后的图片名
     * @return bool|resource
     */
    private function roundnessImage ($img_path)
    {
        if ( ! $src_img = $this->getImgType($img_path)) return false;

        list($o_w, $o_h) = @getimagesize($img_path);

        $width = $this->head_width;
        $high  = $this->head_high;
        $im    = imagecreatetruecolor($width, $high);   // 创建画板，把头像进行放大

        imagecopyresampled($im, $src_img, 0, 0, 0, 0, $width, $high, $o_w, $o_h);

        imagesavealpha($im, true);       // 防止图片失帧

        imagepng($im, $img_path);

        $img = imagecreatetruecolor($width, $high);     // 重新创建一个画板

        imagesavealpha($img, true);     // 防止图片失帧

        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);

        imagefill($img, 0, 0, $bg);

        $r = $width / 2; //圆半径

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $high; $y++) {
                $rgbColor = imagecolorat($im, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        return $img;
    }

    /**
     * 添加图片水印,头像贴在二维码中间
     * @param string $code_image 二维码图片
     * @param string $head_image 头像图片
     * @param string $locate     位置
     * @return array
     */
    private function create_pic_watermark (string $code_image,
                                           string $head_image,
                                           string $locate = 'center') : array
    {
        list($code_width, $code_height) = getimagesize($code_image);
        list($head_width, $head_height) = getimagesize($head_image);


        if ( ! $code = $this->getImgType($code_image)) return ['status' => 0];
        if ( ! $head = $this->getImgType($head_image)) return ['status' => 0];

        switch ($locate) {
            case 'center':
                $x = ($code_width - $head_width) / 2;
                $y = ($code_height - $head_height) / 2;
                break;
            case 'left':
                $x = 1;
                $y = ($code_height - $head_height - 2);
                break;
            case 'right':
                $x = ($code_width - $head_width - 1);
                $y = ($code_height - $head_height - 2);
                break;
            default:
                die("未指定水印位置!");
                break;
        }
        imagecopy($code, $head, $x, $y, 0, 0, $head_width, $head_height);

        @unlink($head_image);                   // 删除临时裁剪图片

        //保存到服务器
        $image_path = self::$image_dir . "24" . time() . ".png";
        imagepng($code, $image_path);          //保存
        imagedestroy($code);
        imagedestroy($head);

        //传回处理好的图片
        return ['status' => 1, 'data' => $image_path];
    }

    /**
     * 判断图片类型创建画布
     * @param string $imagePath 图片地址
     * @return resource|string
     */
    private function getImgType ($imagePath)
    {
        if ( ! $info = @getimagesize($imagePath)) return '';
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $waterImage = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $waterImage = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $waterImage = imagecreatefromgif($imagePath);
                break;
            default:
                $waterImage = '';
                break;
        }
        return $waterImage;
    }
}