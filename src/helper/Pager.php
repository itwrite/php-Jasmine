<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/2/22
 * Time: 12:05
 */

namespace Jasmine\helper;


use Jasmine\library\http\Url;
use Jasmine\util\Arr;

class Pager
{
    protected $total = 0;
    protected $page = 1;
    protected $pageSize = 15;
    protected $numberOfOneSide = 2;

    protected $layerArr = ['[FIRST]', '[PREV]', '[PREV_FIVE]', '[PAGES]', '[NEXT_FIVE]', '[NEXT]', '[LAST]', '[JUMP]', '[TOTAL]'];
    /**
     * @var null
     */
    protected $Url = null;
    protected $data = [];
    protected $config = [
        'var_page' => 'page',
        'var_page_size' => 'page_size',
        'layer' => '',
        'layer_item' => 'li'
    ];

    function __construct($total, $page = 1, $url = '', $pageSize = 15, $config = [])
    {
        $this->Url = new Url();

        $this->setTotal($total);
        $this->setPage($page);
        $this->setPageSize($pageSize);
        $this->Url->parse($url);

        /**
         * 初始化
         */
        $this->config['layer'] = implode('',$this->layerArr);

        /**
         *
         */
        $this->config = Arr::extend($this->config, $config);
    }

    /**
     * @param int $total
     * @return $this
     * itwri 2020/2/22 14:47
     */
    public function setTotal(int $total = 0)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int
     * itwri 2020/2/22 14:47
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $page
     * @return $this
     * itwri 2020/2/22 13:00
     */
    public function setPage(int $page = 1)
    {
        if ($page < 1) {
            $page = 1;
        }
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     * itwri 2020/2/22 13:02
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $pageSize
     * @return $this
     * itwri 2020/2/22 13:02
     */
    public function setPageSize(int $pageSize = 15)
    {
        /**
         * 最小值为1
         */
        $this->pageSize = $pageSize > 1 ? $pageSize : 1;
        return $this;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param $url
     * @return $this
     * itwri 2020/2/22 13:03
     */
    public function setUrl($url)
    {
        $this->Url = new Url($url);
        return $this;
    }

    /**
     * @return string
     * itwri 2020/2/22 13:04
     */
    public function getPageUrl()
    {
        return $this->Url->toString();
    }

    /**
     * 将页面转成url
     * @param $page
     * @return string
     * itwri 2020/2/22 13:32
     */
    public function pageToUrl($page)
    {
        return $this->Url->setParam($this->config['var_page'], $page)->toString();
    }

    /**
     * 返回最后一页的页码
     * @return int
     * itwri 2020/2/22 13:42
     */
    public function getLastPage()
    {
        return (int)ceil($this->getTotal() / $this->getPageSize());
    }

    /**
     * @param string $text
     * @return string
     * itwri 2020/2/22 13:36
     */
    public function getFirstPageHtml($text = '首页')
    {

        /**
         * 如果当前页已经是第一页，则不可用
         */
        if ($this->getPage() == 1) {
            return $this->getDisabledTextWrapper($text);
        }

        return $this->getPageLinkWrapper($this->pageToUrl(1), $text);
    }

    /**
     * @param string $text
     * @return string
     * itwri 2020/2/22 13:52
     */
    public function getLastPageHtml($text = '尾页')
    {
        /**
         * 如果当前页已经是最后一页，则不可用
         */
        if ($this->getPage() == $this->getLastPage()) {
            return $this->getDisabledTextWrapper($text);
        }

        return $this->getPageLinkWrapper($this->pageToUrl($this->getLastPage()), $text);
    }

    /**
     * @param string $text
     * @return string
     * itwri 2020/2/22 13:57
     */
    public function getPreviousPageHtml($text = '上一页')
    {
        /**
         * 如果当前页等于1或者小于1，则不可用
         */
        if ($this->getPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        return $this->getPageLinkWrapper($this->pageToUrl($this->getPage() - 1), $text);
    }

    /**
     * @param string $text
     * @return string
     * itwri 2020/2/22 13:57
     */
    public function getNextPageHtml($text = '下一页')
    {
        /**
         * 如果当前页比最后一页还大，则不可用
         */
        if ($this->getPage() >= $this->getLastPage()) {
            return $this->getDisabledTextWrapper($text);
        }

        return $this->getPageLinkWrapper($this->pageToUrl($this->getPage() + 1), $text);
    }


    /**
     * 前五页
     * @param string $text
     * @return string
     * itwri 2020/2/24 14:21
     */
    protected function getPreviousFivePagesHtml($text = '前五页')
    {


        if ($this->getPage() < 5) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->pageToUrl($this->getPage() - 5);

        return $this->getPageLinkWrapper($url, $text);
    }


    //后五页
    protected function getNextFivePagesHtml($text = '后五页')
    {
        if ($this->getLastPage() < $this->getPage() + 5) {
            return $this->getDisabledTextWrapper($text);

        }
        $url = $this->pageToUrl($this->getPage() + 5);


        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * @param string $okText
     * @return string
     * itwri 2020/2/22 14:48
     */
    public function getGoToPageInputHtml($okText = '确定')
    {
        /**
         * 获预所有参数
         */
        $params = $this->Url->getParam();

        /**
         * 如果存在
         */
        $var_page = $this->config['var_page'];
        if (isset($params[$var_page])) {
            unset($params[$var_page]);
        }
        $url = $this->Url->setParam($params)->toString();

        $form = "<span class='go'><form action='{$url}' style='margin: 0;' method='get' ><span>跳到<input type='number' name='{$var_page}' min='1'/> <button type='submit'>&nbsp;&nbsp;{$okText}&nbsp;&nbsp;</button></span></form></span>";
        return $this->createHtml($this->config['layer_item'], $form);
    }

    /**
     * @param $min
     * @param $max
     * @return string
     * itwri 2020/2/22 17:45
     */
    public function getRangeLinks($min, $max)
    {
        $result = [];
        for ($page = 1; $page <= $this->getLastPage(); $page++) {
            if ($page >= $min && $page <= $max) {
                $result[] = $this->getPageLinkWrapper($this->pageToUrl($page), $page);
            }
        }
        return implode('', $result);
    }

    /**
     * @return string
     * itwri 2020/2/24 10:57
     */
    public function getPagesHtml()
    {
        //当前页放在中间，设置单边数量
        $oneSideSize = $this->numberOfOneSide;
        $bothSideSize = $oneSideSize * 2;
        /**
         * 如果页数小于需要显示的页数
         * 则直接返回所有页
         */
        if ($this->getLastPage() <= $bothSideSize + 1) {
            return $this->getRangeLinks(1, $this->getLastPage());
        }

        if ($this->getPage() < ($oneSideSize + 1)) {
            return $this->getRangeLinks(1, $bothSideSize + 1);
        }
        /**
         * 非上述条件
         * 如果当前页小于一边的数量
         */
        if ($this->getPage() > ($this->getLastPage() - ($oneSideSize + 1))) {
            return $this->getRangeLinks($this->getLastPage() - $bothSideSize, $this->getLastPage());
        }

        return $this->getRangeLinks($this->getPage() - $oneSideSize, $this->getPage() + $oneSideSize);
    }

    /**
     * @return string
     * itwri 2020/2/24 15:08
     */
    public function getTotalPageHtml()
    {
        $span = "<b>共" . $this->getTotal() . "条记录&nbsp;&nbsp;第" . $this->getPage() . "页/共" . $this->getLastPage() . "页</b>";
        return $this->createHtml($this->config['layer_item'], $span, ['class' => 'disabled']);
    }

    /**
     * @return string
     * itwri 2020/2/24 10:57
     */
    public function render()
    {
        return str_replace(
            $this->layerArr,
            [
                $this->getFirstPageHtml(),
                //上一页
                $this->getPreviousPageHtml(),
                //前五页
                $this->getPreviousFivePagesHtml(),
                //页码
                $this->getPagesHtml(),
                //后五页
                $this->getNextFivePagesHtml(),
                //下一页
                $this->getNextPageHtml(),
                //最后一页
                $this->getLastPageHtml(),
                //可以显示跳转到哪页
                $this->getGoToPageInputHtml(),
                //显示数量页码信息
                $this->getTotalPageHtml()
            ], $this->config['layer']);
    }

    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return $this->createHtml($this->config['layer_item'],'<a href="' . htmlentities($url) . '">' . $page . '</a>');
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return $this->createHtml($this->config['layer_item'],"<span>{$text}</span>",['class'=>'disabled']);
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return $this->createHtml($this->config['layer_item'],"<span>{$text}</span>",['class'=>'active']);
    }


    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  mixed $text
     * @return string
     */
    protected function getPageLinkWrapper($url, $text)
    {
        if ($text == $this->getPage()) {
            return $this->getActivePageWrapper($text);
        }

        return $this->getAvailablePageWrapper($url, $text);
    }


    /**
     * 生成html
     * @param $tag
     * @param $value
     * @param array $attributes
     * @param bool $wap
     * @return string
     * itwri 2020/2/22 12:06
     */
    protected function createHtml($tag, $value, $attributes = [], $wap = true)
    {

        if (!$wap) {
            $attributes['value'] = $value;
        }
        $arr = [];

        if (is_array($attributes)) {
            foreach ($attributes as $key => $val) {
                $arr[] = $key . '="' . $val . '"';
            }
        }

        if ($wap == true) {
            return "<{$tag} " . implode(' ', $arr) . ">" . $value . "</{$tag}>";
        }

        return "<{$tag} " . implode(' ', $arr) . "/>";
    }
}