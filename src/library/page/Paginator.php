<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/2/22
 * Time: 12:05
 */

namespace Jasmine\library\page;

use Jasmine\library\http\Url;
use Jasmine\util\Arr;

class Paginator
{
    /**
     * 总数
     * @var int
     */
    protected $total = 0;

    /**
     * 当前页码
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $perPageSize = 15;
    protected $numberOfOneSide = 2;

    /**
     * @var Url|null
     */
    protected $Url = null;

    const FIRST = 'first';
    const PREV = 'prev';
    const PREV_FIVE = 'prev_five';
    const PAGE_ITEM = 'page_item';
    const NEXT_FIVE = 'next_five';
    const NEXT = 'next';
    const LAST = 'last';
    const JUMP = 'jump';
    const TOTAL = 'total';

    protected $config = [
        'var_page' => 'page',
        'var_page_size' => 'page_size',
        'layer' => [self::TOTAL,self::FIRST,self::PREV,self::PREV_FIVE,self::PAGE_ITEM,self::NEXT_FIVE,self::NEXT,self::LAST,self::JUMP],
        'text'=>[
            self::TOTAL => "第{page}页/共{totalPage}页 (共{total}条记录)",
            self::FIRST => "首页",
            self::PREV => "上一页",
            self::PREV_FIVE => "前五页",
            self::NEXT_FIVE => "后五页",
            self::NEXT => "下一页",
            self::LAST => "尾页",
            self::JUMP => "确定"
        ]
    ];

    function __construct($total, $page = 1, $url = '', $perPageSize = 15, $config = [])
    {
        $this->Url = new Url();

        $this->setTotal($total);
        $this->setPage($page);
        $this->setPerPageSize($perPageSize);
        $this->Url->parse($url);

        /**
         *
         */
        $this->config = Arr::extend($this->config, $config);
    }

    /**
     * 设置总数
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
        if ($page >= $this->getLastPage()) {
            $page = $this->getLastPage();
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
     * @param int $perPageSize
     * @return $this
     * itwri 2020/2/22 13:02
     */
    public function setPerPageSize(int $perPageSize = 15)
    {
        /**
         * 最小值为1
         */
        $this->perPageSize = $perPageSize > 1 ? $perPageSize : 1;
        return $this;
    }

    public function getPerPageSize()
    {
        return $this->perPageSize;
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
     * 将页码转成url
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
        return (int)ceil($this->getTotal() / $this->getPerPageSize());
    }

    /**
     * @param $type
     * @return string
     * itwri 2020/2/28 12:45
     */
    protected function getText($type){
        if(isset($this->config['text'][$type])){
            return str_replace(
                ['{page}','{pageSize}','{total}','{totalPage}'],
                [$this->getPage(),$this->getPerPageSize(),$this->getTotal(),$this->getLastPage()],
                $this->config['text'][$type]);
        }
        return '';
    }

    /**
     * @return array
     * itwri 2020/2/22 13:36
     */
    public function getFirstPageItem()
    {

        $text = $this->getText(self::FIRST);

        if ($this->getLastPage() <= $this->numberOfOneSide * 2 + 1) {
            return [];
        }
        /**
         * 如果当前页已经是第一页，则不可用
         */
        if ($this->getPage() == 1) {
            return $this->getDisabledTextWrapper($text, self::FIRST);
        }

        return $this->getPageLinkWrapper($this->pageToUrl(1), $text, self::FIRST);
    }

    /**
     * @return array
     * itwri 2020/2/22 13:52
     */
    public function getLastPageItem()
    {
        $text = $this->getText(self::LAST);

        if ($this->getLastPage() <= $this->numberOfOneSide * 2 + 1) {
            return [];
        }
        /**
         * 如果当前页已经是最后一页，则不可用
         */
        if ($this->getPage() == $this->getLastPage()) {
            return $this->getDisabledTextWrapper($text, self::LAST);
        }

        return $this->getPageLinkWrapper($this->pageToUrl($this->getLastPage()), $text, self::LAST);
    }

    /**
     * @return array
     * itwri 2020/2/22 13:57
     */
    public function getPreviousPageItem()
    {
        $text = $this->getText(self::PREV);
        /**
         * 如果当前页等于1或者小于1，则不可用
         */
        if ($this->getPage() <= 1) {
            return $this->getDisabledTextWrapper($text, self::PREV);
        }

        return $this->getPageLinkWrapper($this->pageToUrl($this->getPage() - 1), $text, self::PREV);
    }

    /**
     * 下一页
     * @return array
     * itwri 2020/2/28 16:05
     */
    public function getNextPageItem()
    {
        $text = $this->getText(self::NEXT);

        /**
         * 如果当前页比最后一页还大，则不可用
         */
        if ($this->getPage() >= $this->getLastPage()) {
            return $this->getDisabledTextWrapper($text, self::NEXT);
        }

        return $this->getPageLinkWrapper($this->pageToUrl($this->getPage() + 1), $text, self::NEXT);
    }


    /**
     * 前五页
     * @return array
     * itwri 2020/2/28 16:05
     */
    protected function getPreviousFivePageItems()
    {
        $text = $this->getText(self::PREV_FIVE);
        if ($this->getPage() < 5) {
            return [];
        }

        $url = $this->pageToUrl($this->getPage() - 5);

        return $this->getPageLinkWrapper($url, $text, self::PREV_FIVE);
    }


    /**
     * @param string $text
     * @return array
     * itwri 2020/2/27 22:09
     */
    protected function getNextFivePageItems()
    {
        $text = $this->getText(self::NEXT_FIVE);
        if ($this->getLastPage() < $this->getPage() + 5) {
            return [];
        }
        $url = $this->pageToUrl($this->getPage() + 5);


        return $this->getPageLinkWrapper($url, $text, self::NEXT_FIVE);
    }

    /**
     *
     * @return array
     * itwri 2020/2/27 22:29
     */
    public function getJumpItem()
    {
        $text = $this->getText(self::JUMP);
        if ($this->getLastPage() <= $this->numberOfOneSide * 2 + 1) {
            return [];
        }
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
        $url = $this->Url->set('query', http_build_query($params))->toString();

        return $this->createPageItem($text, $url, self::JUMP);
    }

    /**
     * @param $min
     * @param $max
     * @return array
     * itwri 2020/2/22 17:45
     */
    protected function getRangeLinks($min, $max)
    {
        $result = [];
        for ($page = 1; $page <= $this->getLastPage(); $page++) {
            if ($page >= $min && $page <= $max) {
                $result[] = $this->getPageLinkWrapper($this->pageToUrl($page), $page);
            }
        }
        return $result;
    }

    /**
     * @return array
     * itwri 2020/2/24 10:57
     */
    public function getPageItems()
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
     * @return array
     * itwri 2020/2/24 15:08
     */
    public function getTotalPageItem()
    {
        $text = $this->getText(self::TOTAL);
        return $this->createPageItem($text, '', self::TOTAL, 'disabled');
    }

    /**
     * @return array
     * itwri 2020/2/24 10:57
     */
    public function toArray()
    {
        $arr = [
            self::FIRST => $this->getFirstPageItem(),
            //上一页
            self::PREV => $this->getPreviousPageItem(),
            //前五页
            self::PREV_FIVE => $this->getPreviousFivePageItems(),
            //页码
            self::PAGE_ITEM => $this->getPageItems(),
            //后五页
            self::NEXT_FIVE => $this->getNextFivePageItems(),
            //下一页
            self::NEXT => $this->getNextPageItem(),
            //最后一页
            self::LAST => $this->getLastPageItem(),
            //可以显示跳转到哪页
            self::JUMP => $this->getJumpItem(),
            //显示数量页码信息
            self::TOTAL => $this->getTotalPageItem()
        ];

        $result = [];
        foreach ($this->config['layer'] as $item) {
            if (isset($arr[$item]) && !empty($arr[$item])) {
                if ($item == self::PAGE_ITEM) {
                    foreach ($arr[$item] as $page) {
                        $result[] = $page;
                    }
                } else {
                    $result[] = $arr[$item];
                }
            }
        }
        return $result;
    }

    /**
     * 生成一个可点击的按钮
     * @param $url
     * @param $text
     * @param string $type
     * @return array
     * itwri 2020/2/27 22:50
     */
    protected function getAvailablePageWrapper($url, $text, $type = self::PAGE_ITEM)
    {
        return $this->createPageItem($text, htmlentities($url), $type);
    }

    /**
     * @param $value
     * @param $url
     * @param $type
     * @param string $status
     * @return array
     * itwri 2020/2/27 22:25
     */
    protected function createPageItem($value, $url, $type, $status = 'normal')
    {
        return ['value' => $value, 'url' => $url, 'type' => $type, 'status' => $status];
    }

    /**
     * 生成一个禁用的按钮
     * @param $text
     * @param string $type
     * @return array
     * itwri 2020/2/27 22:41
     */
    protected function getDisabledTextWrapper($text, $type = self::PAGE_ITEM)
    {
        return $this->createPageItem($text, '', $type, 'disabled');
    }

    /**
     * 生成一个激活的按钮
     * @param $text
     * @param string $type
     * @return array
     * itwri 2020/2/27 22:41
     */
    protected function getActivePageWrapper($text, $type = self::PAGE_ITEM)
    {
        return $this->createPageItem($text, '', $type, 'active');
    }


    /**
     * 生成普通页码按钮
     * @param $url
     * @param $text
     * @param string $type
     * @return array
     * itwri 2020/2/27 22:50
     */
    protected function getPageLinkWrapper($url, $text, $type = self::PAGE_ITEM)
    {
        if ($text == $this->getPage()) {
            return $this->getActivePageWrapper($text, $type);
        }

        return $this->getAvailablePageWrapper($url, $text, $type);
    }
}