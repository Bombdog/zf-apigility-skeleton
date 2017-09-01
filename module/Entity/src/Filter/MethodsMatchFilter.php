<?php
namespace Entity\Filter;

use Zend\Hydrator\Filter\FilterInterface;


/**
 * This filter exclude any methods that have a name in the array
 */
class MethodsMatchFilter implements FilterInterface
{
	/**
	 * The methods to exclude
	 *
	 * @var array
	 */
	protected $methods;

	/**
	 * Either an exclude or an include
	 * @var bool
	 */
	protected $exclude = null;

	/**
	 * @param array|string $methods The methods to exclude
	 * @param bool $exclude If the method should be excluded
	 */
	public function __construct($methods, $exclude = true)
	{
		// Performance optimization, as it allows us to do isset instead of in_array
		$this->methods = array_flip((array) $methods);
		$this->exclude = $exclude;
	}

	/**
	 * Should return true, if the given filter
	 * does not match
	 *
	 * @param string $property The name of the property
	 * @return bool
	 */
	public function filter( $property )
	{
		$pos = strpos($property, '::');
		if ($pos !== false) {
			$pos += 2;
		} else {
			$pos = 0;
		}
		if (isset($this->methods[substr($property, $pos)])) {
			return $this->exclude ? false : true;
		}
		return $this->exclude ? true : false;
	}
}