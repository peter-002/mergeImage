# mergeImage

此demo使用phpGD库完成，用于小程序二维码的裁剪、背景图合成、文字添加

> 代码实例

```
$merge = new \ImageMerge('static/mini/resource/bg_img.png', 'path/appcode.png');
$url   = $merge->changeImageLogo('https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTLF3zZPSXGkXQibjoFaX4iaS6lmyAib8bwKoqtXV3lKkBPHs');

$merge->auxiliary_image = $url['data'];

$result = $merge->mergeImage()['data'];

$merge->master_image = $result;

$name     = 'sorbon';
$interval = mb_strlen($name);   // 第二个字符的x轴 需要乘以 第一个字符的长度
$conf     = [
    [
        'text'             => $name,
        'font_url'         => 'font/PingFang Heavy.ttf',
        'font_size'        => 54 * 1.4,
        'angle'            => 0,
        'font_color'       => ['r' => 255, 'g' => 235, 'b' => 134],
        'font_coordinates' => ['x' => 140, 'y' => 60 * 2 + 54 * 1.8],
    ],
    [
        'text'             => '技术',
        'font_url'         => 'font/PingFang Heavy.ttf',
        'font_size'        => 28 * 1.6,
        'angle'            => 0,
        'font_color'       => ['r' => 255, 'g' => 255, 'b' => 255],
        'font_coordinates' => ['x' => 140 + 54 * $interval * 1.9 + 40, 'y' => 62 * 2 + 54 * 1.8],
    ],
    [
        'text'             => '13812340000',
        'font_url'         => 'font/SF-Pro-Text-Semibold.otf',
        'font_size'        => 28 * 2,
        'angle'            => 0,
        'font_color'       => ['r' => 255, 'g' => 255, 'b' => 255],
        'font_coordinates' => ['x' => 140, 'y' => 170 * 2],
    ],
    [
        'text'             => '深圳xx科技有限公司',
        'font_url'         => 'font/PingFang Regular.ttf',
        'font_size'        => 20 * 2,
        'angle'            => 0,
        'font_color'       => ['r' => 255, 'g' => 255, 'b' => 255],
        'font_coordinates' => ['x' => 140, 'y' => 312 * 2],
    ],
    [
        'text'             => '扫码深度了解我',
        'font_url'         => 'font/PingFang Heavy.ttf',
        'font_size'        => 20 * 2,
        'angle'            => 0,
        'font_color'       => ['r' => 178, 'g' => 178, 'b' => 178],
        'font_coordinates' => ['x' => 456, 'y' => 148
    ]
];
```
> 效果

![背景图](./resource.png)

![目标图](/target.png)