<?php
/**
 * Created by PhpStorm.
 * User: parfenov
 * Date: 13.02.2019
 * Time: 12:46
 */

namespace App\Model\Exception;


use Throwable;
use Zend\Code\Exception\RuntimeException;

class MessageNotFoundException extends RuntimeException
{


	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		$message = sprintf("Сообщение с ID %s не найдено", $message);
		parent::__construct($message, $code, $previous);
	}


}