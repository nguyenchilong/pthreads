<?php
class ScopeTest extends Thread {
	public function run(){
		/* set a variable to test fetching from an imported thread */
		$this->my = "data";
		
		printf("%s: %lu running\n", __CLASS__, $this->getThreadId());
		printf("%s: %lu notified: %d\n", __CLASS__, $this->getThreadId(), $this->wait());
		
		$this->my = strrev($this->my);
	}
}

class ScopeTest2 extends Thread {
	public function __construct($other){
		$this->other = $other;
	}
	
	public function run(){
		printf("%s: %lu running\n", __CLASS__, $this->getThreadId());
		if (($other = Thread::getThread($this->other))) {
			printf("%s: %lu working ... %s\n", __CLASS__, $this->getThreadId(), $other->my);
			usleep(1000000); /* simulate some work */
			if ($other->isWaiting())
				printf("%s: %lu notifying %lu: %d\n", __CLASS__, $this->getThreadId(), $this->other, $other->notify());
			$other->join();
			printf("%s: %lu testing again ... %s\n", __CLASS__, $this->getThreadId(), $other->my);
		} else {
			printf("%s: %lu failed to find %lu\n", __CLASS__, $this->getThreadId(), $this->other);
		}
		printf("%s: %lu notified: %d\n", __CLASS__, $this->getThreadId(), $this->wait());
	}
}

printf("Process: running\n");
$test = new ScopeTest();
$test->start();
$test2 = new ScopeTest2($test->getThreadId());
$test2->start();

printf("Process: notifying %lu: %d\n", $test2->getThreadId(), $test2->notify());

if ($test->isWaiting()) {
	/*
	* If importing has been disabled then the responsability to notify the waiting thread must fall back to the Process to avoid deadlock
	*/
	printf("Process: notifying %lu: %d\n", $test->getThreadId(), $test->notify());
}
?>