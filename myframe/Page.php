<?php
namespace myframe;

class Page
{
    /**
     * 生成分页导航HTML
     * @param string $url 链接地址
     * @param int $total 总记录数
     * @param init $page 当前页码值
     * @param int $size 每页显示的条数
     * @param int $num 导航中的页码数
     * @return string 生成的HTML结果
     */
    public static function html($url, $total, $page, $size, $num = 5)
    {
        // 当前访问的页码，最低为1
        $page = max((int)$page, 1);
        // 计算总页数(最大页码)
        $maxpage = max(ceil($total / $size), 1);
        // 计算当前页前后显示的相关链接个数
        $num = floor($num / 2);
        // 如果只有一页则页码器为空
        if ($maxpage == 1) {
            return '';
        }
        // 生成页码器中首页和上一页的HTML
        $html = ["<ul class=\"pagination\">"];
        if ($page > 1) {
            $html[] = "<li><a href=\"{$url}1\">首页</a></li>";
            $html[] = '<li><a href="' . $url . ($page - 1) . '">上一页</a></li>';
        } else {
            $html[] = '<li class="disabled"><span>首页</span></li>';
            $html[] = '<li class="disabled"><span>上一页</span></li>';
        }
        //计算页码链接中开始页码
        $start = $page - $num;
        //计算页码链接中结束页码
        $end = $page + $num;
        //判断开始和结束页码链接的边界
        if ($start < 1) {
            //调整页码链接的结束页码
            $end = $end + (1 - $start);
            $start = 1;
        }
        if ($end > $maxpage) {
            //调整页码链接的开始页码
            $start = $start - ($end - $maxpage);
            if ($start < 1) {
                $start = 1;
            }
            $end = $maxpage;
        }
        //生成页码器中“...”的HTML
        if ($start > 1) {
            $html[] = '<li class="disabled"><span>...</span></li>';
        }
        //生成页码链接的HTML
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $page) {
                $html[] = "<li class=\"active\"><span>$i</span></li>";
            } else {
                $html[] = "<li><a href=\"{$url}{$i}\">$i</a></li>";
            }
        }
        //生成页码器中“...”的HTML
        ($end < $maxpage) && $html[] = '<li class="disabled"><span>...</span></li>';

        // 生成页码器中下一页和尾页的HTML
        if ($page == $maxpage) {
            $html[] = '<li class="disabled"><span>下一页</span></li>';
            $html[] = '<li class="disabled"><span>尾页</span></li>';
        } else {
            $html[] = '<li><a href="' . $url. ($page + 1) . '">下一页</a></li>';
            $html[] = "<li><a href=\"{$url}{$maxpage}\">尾页</a></li>";
        }
        $html[] = '</ul>';
        //返回页码器的HTML
        return implode('', $html);
    }
}
