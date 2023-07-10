<?php

declare(strict_types=1);

namespace App\Utils;

trait EnvTrait
{
	private $env;

	public function setEnv(string $env)
	{
		$this->env = $env;
	}

	public function getEnv() : string
	{
		return $this->env;
	}
}