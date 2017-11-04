<?php

/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/10/27
 * Time: 14:44
 */
class Page {
    //记录总数
    protected $itemCount;

    //页面总数
    protected $pageCount;

    //当前页面
    protected $currPage = 1;

    //每页显示页数
    protected $itemPerPage = 20;

    //页面Query参数
    protected $queryUrl;

    //页面Query参数
    protected $queryParams;

    //是否重写
    protected $isRewrite;

    //重写分隔符
    protected $separator;

    /*
     * 分页类构造函数
     * @param int $currPage 当前页数
     * @param int $itemCount 分页记录总数
     * @param int $itemPerPage 每页记录数
     * @param string $queryUrl 分页链接地址
     * @param string $queryParams 当前分面的QueryString参数
     * return 返回 Paginator对象
     */
    public function __construct(
        $currPage,
        $itemCount,
        $itemPerPage = 20,
        $queryUrl,
        $queryParams,
        $isRewrite = false,
        $separator = '-'
    ) {
        $this->itemCount = $itemCount;
        $this->itemPerPage = $itemPerPage > 0 ? $itemPerPage : 1;
        $this->pageCount = ceil($this->itemCount / $this->itemPerPage);
        $this->currPage = $currPage > $this->pageCount ? $this->pageCount : ($currPage <= 0 ? 1 : $currPage);

        $this->queryUrl = $queryUrl;
        $this->queryParams = $queryParams;

        $this->isRewrite = $isRewrite;
        $this->separator = $separator;
    }

    /*
     * 获取$style分格的分页HTML
     * @param int $style  分页风格id
     * return string 分页HTML
     */
    public function getHtml($style) {
        $html = '';
        switch ($style) {
            case 1:
                $html = $this->getStyleOneHtml();
                break;
            case 2:
                $html = $this->getStyleTwoHtml();
                break;
            case 3:
                $html = $this->getStyleThreeHtml();
                break;
            case 4:
                $html = $this->getStyleFourHtml();
                break;
        }

        return $html;
    }

    public function getPageCount() {
        return $this->pageCount;
    }

    public function getCurrentPage() {
        return $this->currPage;
    }

    public function getItemPerPage() {
        return $this->itemPerPage;
    }

    /**
     * 返回limit查询所需offset值
     * return int limit offset值
     */
    public function getOffset() {
        return $this->itemCount === 0 ? 0 : $this->getCurrentPage() * $this->itemPerPage - $this->itemPerPage;
    }

    /**
     * 样式： 4条记录1/1页  上一页 1 2 ... 4 5 6 7 8 ... 下一页 末页
     * return string 分页样式一 HTML代码
     */
    private function getStyleOneHtml() {
        $html = '';
        $offsetPageNum = 2;
        $beginPage = 1;
        $startMargin = 5;
        $endPage = $this->pageCount >= $startMargin ? $startMargin : $this->pageCount;

        if ($this->pageCount > 0) {
            $html = '<nav class="pagination"><span><mark>' . $this->itemCount . '</mark>条记录' . $this->currPage . '/' . $this->pageCount . '页</span>';
            if ($this->currPage > 1) {
                $html .= ' <a href="' . $this->buildPageUrl($this->currPage - 1) . '">上一页</a>';
            }

            if ($this->currPage > $startMargin) {
                $html .= ' <a href="' . $this->buildPageUrl(1) . '">1</a>';
                $html .= ' <a href="' . $this->buildPageUrl(2) . '">2</a> ... ';
            }

            if ($this->currPage >= $startMargin) {
                $endPage = $this->currPage + $offsetPageNum;
            }
            if ($this->currPage > $startMargin) {
                $beginPage = $this->currPage - $offsetPageNum;
            }

            if ($endPage > $this->pageCount) {
                $beginPage = $this->pageCount - $startMargin + 1;
                $endPage = $this->pageCount;
            }

            if ($beginPage < 1) {
                $beginPage = 1;
            }

            for ($p = $beginPage; $p <= $endPage; $p++) {
                $currClass = $this->currPage == $p ? ' class="cur"' : '';
                $url = !empty($currClass) ? 'javascript:;' : $this->buildPageUrl($p);
                $html .= ' <a href="' . $url . '"' . $currClass . '>' . $p . '</a>';
            }

            if ($this->currPage < $this->pageCount - $offsetPageNum && $this->pageCount > $startMargin) {
                $html .= ' ... ';
            }

            if ($this->currPage < $this->pageCount) {
                $html .= ' <a href="' . $this->buildPageUrl($this->currPage + 1) . '">下一页</a>';
            }

            if (($this->currPage + $startMargin - 2) <= $this->pageCount) {
                $html .= ' <a href="' . $this->buildPageUrl($this->pageCount) . '">末页</a>';
            }

            $html .= '</nav>';
        }

        return $html;
    }

    /**
     * 样式： 上一页 1 2 ... 4 5 6 7 8 ... 下一页
     * return string 分页样式一 HTML代码
     */
    private function getStyleTwoHtml() {
        $html = '';
        $offsetPageNum = 2;
        $beginPage = 1;
        $startMargin = 5;
        $endPage = $this->pageCount >= $startMargin ? $startMargin : $this->pageCount;

        if ($this->pageCount > 1) {
            $html = '<nav class="pagination">';
            if ($this->currPage > 1) {
                $html .= ' <a href="' . $this->buildPageUrl(1) . '" class="page">首页</a>';
                $html .= ' <a href="' . $this->buildPageUrl($this->currPage - 1) . '" class="page">上一页</a>';
            }
            $html .= "<ul>";
            if ($this->currPage > $startMargin) {
                $html .= ' <li><a href="' . $this->buildPageUrl(1) . '">1</a></li>';
                $html .= ' <li><a href="' . $this->buildPageUrl(2) . '">2</a></li> <li>...</li> ';
            }

            if ($this->currPage >= $startMargin) {
                $endPage = $this->currPage + $offsetPageNum;
            }
            if ($this->currPage > $startMargin) {
                $beginPage = $this->currPage - $offsetPageNum;
            }

            if ($endPage > $this->pageCount) {
                $beginPage = $this->pageCount - $startMargin + 1;
                $endPage = $this->pageCount;
            }

            if ($beginPage < 1) {
                $beginPage = 1;
            }

            for ($p = $beginPage; $p <= $endPage; $p++) {
                $currClass = $this->currPage == $p ? ' class="active"' : '';
                $url = !empty($currClass) ? 'javascript:;' : $this->buildPageUrl($p);
                $html .= ' <li' . $currClass . '><a href="' . $url . '">' . $p . '</a></li>';
            }

            if ($this->currPage < $this->pageCount - $offsetPageNum && $this->pageCount > $startMargin) {
                $html .= '<li>...</li>';
            }

            $html .= "</ul>";

            if ($this->currPage < $this->pageCount) {
                $html .= ' <a href="' . $this->buildPageUrl($this->currPage + 1) . '" class="page">下一页</a>';
                $html .= ' <a href="' . $this->buildPageUrl($this->pageCount) . '" class="page">末页</a>';
            }

            if (($this->currPage + $startMargin - 2) <= $this->pageCount) {
                //$html .= ' <a href="'.$this->queryUrl. $this->buildPageQuery($this->pageCount).'">末页</a>';
            }

            $html .= '</nav>';
        }

        return $html;
    }

    /**
     * 样式：  1 2 ... 4 5 6 7 8 > 共14页 第[]页 确定
     * return string 分页样式三 HTML代码
     */
    private function getStyleThreeHtml() {
        $html = '';
        $offsetPageNum = 2;
        $beginPage = 1;
        $startMargin = 5;
        $endPage = $this->pageCount >= $startMargin ? $startMargin : $this->pageCount;

        if ($this->pageCount > 1) {
            $html = '<nav class="pagination">';
            if ($this->currPage > 1) {
                $html .= '<a href="' . $this->buildPageUrl($this->currPage - 1) . '" class="prev"></a>';
            }

            if ($this->currPage > $startMargin) {
                $html .= '<a href="' . $this->buildPageUrl(1) . '">1</a>';
                $html .= '<a href="' . $this->buildPageUrl(2) . '">2</a><span>...</span>';
            }

            if ($this->currPage >= $startMargin) {
                $endPage = $this->currPage + $offsetPageNum;
            }
            if ($this->currPage > $startMargin) {
                $beginPage = $this->currPage - $offsetPageNum;
            }

            if ($endPage > $this->pageCount) {
                $beginPage = $this->pageCount - $startMargin + 1;
                $endPage = $this->pageCount;
            }

            if ($beginPage < 1) {
                $beginPage = 1;
            }

            for ($p = $beginPage; $p <= $endPage; $p++) {
                $currClass = $this->currPage == $p ? ' class="cur"' : '';
                $url = !empty($currClass) ? 'javascript:;' : $this->buildPageUrl($p);
                $html .= '<a href="' . $url . '"' . $currClass . '">' . $p . '</a>';
            }

            if ($this->currPage < $this->pageCount - $offsetPageNum && $this->pageCount > $startMargin) {
                $html .= '<span>...</span>';
            }

            if ($this->currPage < $this->pageCount) {
                $html .= '<a href="' . $this->buildPageUrl($this->currPage + 1) . '" class="next"></a>';
            }

            if (($this->currPage + $startMargin - 2) <= $this->pageCount) {
                //$html .= ' <a href="'.$this->queryUrl. $this->buildPageQuery($this->pageCount).'">末页</a>';
            }
            $html .= '<aside>共<mark>' . $this->pageCount . '</mark>页</aside>';
            $html .= '</nav><form method="get"><span>第</span><input class="text" name="p" type="text" placeholder="几" />';
            $html .= '<span>页</span><input class="submit" type="submit" value="确定" /></form>';
        }

        return $html;
    }

    /**
     *                             <ul class="pagination">
     * <li><span>200条记录</span></li>
     * <li><a>上一页</a></li>
     * <li class="active"><a href="#">1</a></li>
     * <li><a>2</a></li>
     * <li><a href="#">下一页</a></li>
     * <li><span>跳转</span><span style="padding:0px;border:0px"><input type="text" class="form-control jump" placeholder="1"></span></li>
     * </ul>
     */
    /**
     * 样式：  1 2 ... 4 5 6 7 8 > 共14页 第[]页 确定
     * return string 分页样式三 HTML代码
     */
    private function getStyleFourHtml() {
        $html = '';
        $offsetPageNum = 2;
        $beginPage = 1;
        $startMargin = 5;
        $endPage = $this->pageCount >= $startMargin ? $startMargin : $this->pageCount;

        if ($this->pageCount > 1) {
            $html = '<ul class="pagination"><li><span>共' . $this->pageCount . '页 ' . $this->itemCount . '条记录</span></li>';
            if ($this->currPage > 1) {
                $html .= '<li><a href="' . $this->buildPageUrl($this->currPage - 1) . '">上一页</a></li>';
            }

            if ($this->currPage > $startMargin) {
                $html .= '<li><a href="' . $this->buildPageUrl(1) . '">1</a></li>';
                $html .= '<li><a href="' . $this->buildPageUrl(2) . '">2</a></li><li><span>...</span></li>';
            }

            if ($this->currPage >= $startMargin) {
                $endPage = $this->currPage + $offsetPageNum;
            }
            if ($this->currPage > $startMargin) {
                $beginPage = $this->currPage - $offsetPageNum;
            }

            if ($endPage > $this->pageCount) {
                $beginPage = $this->pageCount - $startMargin + 1;
                $endPage = $this->pageCount;
            }

            if ($beginPage < 1) {
                $beginPage = 1;
            }

            for ($p = $beginPage; $p <= $endPage; $p++) {
                $currClass = $this->currPage == $p ? ' class="active"' : '';
                $url = !empty($currClass) ? 'javascript:;' : $this->buildPageUrl($p);
                $html .= '<li' . $currClass . '><a href="' . $url . '">' . $p . '</a></li>';
            }

            if ($this->currPage < $this->pageCount - $offsetPageNum && $this->pageCount > $startMargin) {
                $html .= '<li><span>...</span></li>';
            }

            if (($this->currPage + $startMargin - 2) <= $this->pageCount) {
                $html .= '<li><a href="' . $this->buildPageUrl($this->pageCount) . '">' . $this->pageCount . '</a></li>';
            }

            if ($this->currPage < $this->pageCount) {
                $html .= '<li><a href="' . $this->buildPageUrl($this->currPage + 1) . '">下一页</a></li>';
            }

            $html .= '<li><a id="jump">跳转</a><span style="padding:0px;border:0px"><input type="text" class="form-control jump" value="1"></span></li></ul>';
        }

        return $html;
    }

    /**
     * @param int $page 分页面数
     * @return string 分页链接
     */
    private function buildPageUrl($page) {
        if (!$this->isRewrite) {
            $query = empty($this->queryParams) ? "?page=$page" : '?' . http_build_query($this->queryParams) . "&page=$page";
            $query = $this->queryUrl . $query;
        } else {
            $params = is_array($this->queryParams) && count($this->queryParams) > 0 ? '?' . http_build_query($this->queryParams) : '';
            $query = str_replace('page_position', $page, $this->queryUrl) . $params;
        }

        return $query;
    }
}