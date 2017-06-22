<?php

namespace Wingwah\Helpers;

trait DBStash {
	/**
	 * @see Wingwah\Helpers\User() for methods reference
	 */
	use \Wingwah\Providers\User;

	public function equals($count)
	{
		if ($this->count==(int)$count) {
			$this->set = true;
			$this->redirect = true;
			return $this;
		}

		return $this;
	}
}