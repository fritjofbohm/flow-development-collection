<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\AOP;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package FLOW3
 * @subpackage AOP
 * @version $Id$
 */

require_once('Fixture/F3_FLOW3_Tests_AOP_Fixture_MethodsTaggedWithSomething.php');

/**
 * Testcase for the Pointcut Method-Tagged-With Filter
 *
 * @package FLOW3
 * @subpackage AOP
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PointcutMethodTaggedWithFilterTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function matchesTellsIfTheSpecifiedRegularExpressionMatchesTheGivenTag() {
		$className = 'F3\FLOW3\Tests\AOP\Fixture\MethodsTaggedWithSomething';

		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\Service', array('loadFromCache', 'saveToCache'), array(), '', FALSE, TRUE);
		$mockReflectionService->initialize(array($className));

		$methodTaggedWithFilter = new \F3\FLOW3\AOP\PointcutMethodTaggedWithFilter('someMethod');
		$methodTaggedWithFilter->injectReflectionService($mockReflectionService);
		$this->assertTrue($methodTaggedWithFilter->matches(__CLASS__, 'someMethod', $className, 1));

		$methodTaggedWithFilter = new \F3\FLOW3\AOP\PointcutMethodTaggedWithFilter('some.*');
		$methodTaggedWithFilter->injectReflectionService($mockReflectionService);
		$this->assertTrue($methodTaggedWithFilter->matches(__CLASS__, 'someMethod', $className, 1));
		$this->assertTrue($methodTaggedWithFilter->matches(__CLASS__, 'someOtherMethod', $className, 2));

		$methodTaggedWithFilter = new \F3\FLOW3\AOP\PointcutMethodTaggedWithFilter('some.*');
		$methodTaggedWithFilter->injectReflectionService($mockReflectionService);
		$this->assertFalse($methodTaggedWithFilter->matches(__CLASS__, 'somethingCompletelyDifferent', $className, 1));
	}
}
?>