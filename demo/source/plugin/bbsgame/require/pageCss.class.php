<?php
/*
 * author AgudaZaric
 * e-mail coderzl@hotmail.com
 *     QQ 384318815
 *
 * 分页类
 * 待扩展。
 *
 * */
class pageCss {

	private $pageSize, //每页显示记录数
			$scope, //前后显示页码范围
			$url,	//跳转地址
			$style, //分页风格
			$currentPage, //当前页
			$counts;	//记录总数

    function PageCss($pageSize, $scope, $url, $style=0) {
    	$this->pageSize = $pageSize;
    	$this->scope = $scope;
    	$this->url = $url;
    	$this->style = $style;
    }


	 private function generateHTML() {
	 	if(!$this->counts) return false;
	 	!$this->currentPage && $this->currentPage = 1;
		$this->validateScope();

		$queueLen = ceil($this->counts/$this->pageSize);

		$queue = $this->getQueenWithCurrentpage($queueLen);

	 	$pre = $this->currentPage-1;
	 	$this->currentPage <= 1 && $pre = 1;
	 	$suf = $this->currentPage+1;
	 	$this->currentPage+1 >= $queueLen && $suf = $queueLen;

		$pageCss = "<a href='".$this->url."1'><<<</a><a href='$this->url$pre'>上一页</a>&nbsp;&nbsp;";

		for($index=$queue['head']; $index<=$queue['tail']; $index++) {
			if($index > 0) {
				if($index == $this->currentPage) $pageCss .= "<a href='$this->url$index'>$index</a>&nbsp;&nbsp;";
				else $pageCss .= "<a href='$this->url$index'>$index</a>&nbsp;&nbsp;";
			}
		}
		$pageCss .= "<a href='$this->url$suf'>下一页</a><a href='$this->url$queueLen'>>>></a>";
		return $pageCss;
	 }

	 /**
	  * Validate scope value,If iilegal fixed auto
	  *
	  */
	 private function validateScope() {
	 	!$this->scope && $this->scope = floor(($this->counts-$this->pageSize)/$this->pageSize);
	 	$this->scope > floor($this->counts/2) && $this->scope = floor(($this->counts-$this->pageSize)/$this->pageSize);
		$this->scope < 0 && $this->scope = 1;
	 }

	/**
	 * Return a queue ,Set keys value with current page.
	 * @param int $queueLen
	 *
	 * @return arr $queue
	 */
	 private function getQueenWithCurrentpage($queueLen){
		$head = $this->currentPage-$this->scope;
	  	$tail = $this->currentPage+$this->scope;

	 	if($this->currentPage <= $this->scope ) { //位于队列前半部
	 		 $tail = $this->scope*2 + 1;
	 		 $tail > $queueLen && $tail = $queueLen; //超出队列长度，指向队列尾部。
	 		 $head = 1;
	 	}else if($this->currentPage >= $queueLen) { //溢出
	 		$current = $tail = $queueLen;
	 		$head = $queueLen - $this->scope*2;
	 	}else if($this->currentPage > $queueLen - $this->scope && $this->currentPage < $queueLen) {//后半部
	 		$tail = $queueLen;
	 		$head = $queueLen - $this->scope*2;
	 	}
	 	$queue['head'] = $head;
	 	$queue['tail'] = $tail;
	 	return $queue;
	 }


	 public function getHTML() {
		return $this->generateHTML();
	 }

	 public function setCounts($counts) {
	 	$this->counts = $counts;
	 }

	 public function setCurrentpage($currentPage) {
		$this->currentPage = $currentPage;
	 }
}
?>