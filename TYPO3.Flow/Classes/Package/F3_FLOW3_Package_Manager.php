<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Package;

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
 * @subpackage Package
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */

/**
 * The default TYPO3 Package Manager
 *
 * @package FLOW3
 * @subpackage Package
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Manager implements \F3\FLOW3\Package\ManagerInterface {

	/**
	 * @var array Array of available packages, indexed by package key
	 */
	protected $packages = array();

	/**
	 * @var array A translation table between lower cased and upper camel cased package keys
	 */
	protected $packageKeys = array();

	/**
	 * @var array List of active packages - not used yet!
	 */
	protected $arrayOfActivePackages = array();

	/**
	 * @var array Array of packages whose classes must not be registered as objects automatically
	 */
	protected $objectRegistrationPackageBlacklist = array();

	/**
	 * @var array Array of class names which must not be registered as objects automatically. Class names may also be regular expressions.
	 */
	protected $objectRegistrationClassBlacklist = array(
		'F3\FLOW3\AOP::.*',
		'F3\FLOW3\Object.*',
		'F3\FLOW3\Package.*',
		'F3\FLOW3\Reflection.*',
	);

	/**
	 * Initializes the package manager
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function initialize() {
		$this->packages = $this->scanAvailablePackages();
		foreach (array_keys($this->packages) as $upperCamelCasedPackageKey) {
			$this->packageKeys[strtolower($upperCamelCasedPackageKey)] = $upperCamelCasedPackageKey;
		}
	}

	/**
	 * Returns TRUE if a package is available (the package's files exist in the pcakages directory)
	 * or FALSE if it's not. If a package is available it doesn't mean neccessarily that it's active!
	 *
	 * @param string $packageKey: The key of the package to check
	 * @return boolean TRUE if the package is available, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function isPackageAvailable($packageKey) {
		if (!is_string($packageKey)) throw new \InvalidArgumentException('The package key must be of type string, ' . gettype($packageKey) . ' given.', 1200402593);
		return (isset($this->packages[$packageKey]));
	}

	/**
	 * For the time being this is an alias of isPackageAvailable() - until a real implemenation exists.
	 *
	 * @param string $packageKey The key of the package to check
	 * @return boolean TRUE if the package is active, otherwise FALSE
	 */
	public function isPackageActive($packageKey) {
		return $this->isPackageAvailable($packageKey);
	}

	/**
	 * Returns a \F3\FLOW3\Package\PackageInterface object for the specified package.
	 * A package is available, if the package directory contains valid meta information.
	 *
	 * @param string $packageKey
	 * @return \F3\FLOW3\Package The requested package object
	 * @throws \F3\FLOW3\Package\Exception\UnknownPackage if the specified package is not known
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPackage($packageKey) {
		if (!$this->isPackageAvailable($packageKey)) throw new \F3\FLOW3\Package\Exception\UnknownPackage('Package "' . $packageKey . '" is not available.', 1166546734);
		return $this->packages[$packageKey];
	}

	/**
	 * Returns an array of \F3\FLOW3\Package\Meta objects of all available packages.
	 * A package is available, if the package directory contains valid meta information.
	 *
	 * @return array Array of \F3\FLOW3\Package\Meta
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getAvailablePackages() {
		return $this->packages;
	}

	/**
	 * Returns an array of \F3\FLOW3\Package\Meta objects of all active packages.
	 * A package is active, if it is available and has been activated in the package
	 * manager settings.
	 *
	 * @return array Array of \F3\FLOW3\Package\Meta
	 * @author Robert Lemke <robert@typo3.org>
	 * @todo Implement activation / deactivation of packages
	 */
	public function getActivePackages() {
		return $this->packages;
	}

	/**
	 * Returns the upper camel cased version of the given package key or FALSE
	 * if no such package is available.
	 *
	 * @param string $lowerCasedPackageKey The package key to convert
	 * @return mixed The upper camel cased package key or FALSE if no such package exists
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getCaseSensitivePackageKey($unknownCasedPackageKey) {
		$lowerCasedPackageKey = strtolower($unknownCasedPackageKey);
		return (isset($this->packageKeys[$lowerCasedPackageKey])) ? $this->packageKeys[$lowerCasedPackageKey] : FALSE;
	}

	/**
	 * Returns the absolute path to the root directory of a package. Only
	 * works for package which have a proper meta.xml file - which they
	 * should.
	 *
	 * @param string $packageKey: Name of the package to return the path of
	 * @return string Absolute path to the package's root directory
	 * @throws \F3\FLOW3\Package\Exception\UnknownPackage if the specified package is not known
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPackagePath($packageKey) {
		if (!$this->isPackageAvailable($packageKey)) throw new \F3\FLOW3\Package\Exception\UnknownPackage('Package "' . $packageKey . '" is not available.', 1166543253);
		return $this->packages[$packageKey]->getPackagePath();
	}

	/**
	 * Returns the absolute path to the "Classes" directory of a package.
	 *
	 * @param string $packageKey: Name of the package to return the "Classes" path of
	 * @return string Absolute path to the package's "Classes" directory, with trailing directory separator
	 * @throws \F3\FLOW3\Package\Exception\UnknownPackage if the specified package is not known
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getPackageClassesPath($packageKey) {
		if (!$this->isPackageAvailable($packageKey)) throw new \F3\FLOW3\Package\Exception\UnknownPackage('Package "' . $packageKey . '" is not available.', 1167574237);
		return $this->packages[$packageKey]->getClassesPath();
	}

	/**
	 * Scans all directories in the Packages/ directory for available packages.
	 * For each package a \F3\FLOW3\Package:: object is created and returned as
	 * an array.
	 *
	 * @return array An array of \F3\FLOW3\Package\Package objects for all available packages
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function scanAvailablePackages() {
		$availablePackagesArr = array();

		$availablePackagesArr['FLOW3'] = new \F3\FLOW3\Package\Package('FLOW3', FLOW3_PATH_PACKAGES . 'FLOW3/');

		$packagesDirectoryIterator = new \DirectoryIterator(FLOW3_PATH_PACKAGES);
		while ($packagesDirectoryIterator->valid()) {
			$filename = $packagesDirectoryIterator->getFilename();
			if ($filename{0} != '.' && $filename != 'FLOW3') {
				$packagePath = \F3\FLOW3\Utility\Files::getUnixStylePath($packagesDirectoryIterator->getPathName()) . '/';
				$availablePackagesArr[$filename] = new \F3\FLOW3\Package\Package($filename, $packagePath);
			}
			$packagesDirectoryIterator->next();
		}
		return $availablePackagesArr;
	}
}

?>