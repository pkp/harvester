<?php
/**
 * @defgroup controllers_api_harvester
 */

/**
 * @file controllers/api/harvester/HarvesterApiHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HarvesterApiHandler
 * @ingroup controllers_api_harvester
 *
 * @brief Class defining the headless API for backend archive manipulation.
 */

// import the base Handler
import('lib.pkp.classes.handler.PKPHandler');

class HarvesterApiHandler extends PKPHandler {
	/**
	 * Constructor.
	 */
	function HarvesterApiHandler() {
		parent::PKPHandler();
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPProcessAccessPolicy');
		$this->addPolicy(new PKPProcessAccessPolicy($request, $args, 'harvest'));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Harvest archives.
	 *
	 * NB: This handler method is meant to be called by the parallel
	 * processing framework (see ProcessDAO::spawnProcesses()). Executing
	 * this handler in parallel will significantly improve harvesting
	 * performance.
	 *
	 * The 'harvesting_checking_max_processes' config parameter limits
	 * the number of parallel processes that can be started in parallel.
	 *
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function harvest($args, $request) {
		// This is potentially a long running request. So
		// give us unlimited execution time.
		ini_set('max_execution_time', 0);

		// Get the process id.
		$processId = $args['authToken'];

		// Run until all archives have been harvested.
		$processDao = DAORegistry::getDAO('ProcessDAO');
		$archiveDao = DAORegistry::getDAO('ArchiveDAO');
		$plugins = PluginRegistry::loadCategory('harvesters');

		do {
			// Check that the process lease has not expired.
			$continue = $processDao->canContinue($processId);

			if ($continue) {
				$process = $processDao->getObjectById($processId);
				$archive = $archiveDao->getNextFlaggedArchive($request, $processId);
				if (!$archive) $continue = false;
				else {
					$plugin = $plugins[$archive->getHarvesterPluginName()];
					$plugin->updateIndex($archive, $process->getAdditionalData());
				}
			}
		} while ($continue);

		// Free the process slot.
		$processDao->deleteObjectById($processId);

		// This request returns just a (private) status message.
		return 'Done!';
	}
}

?>
