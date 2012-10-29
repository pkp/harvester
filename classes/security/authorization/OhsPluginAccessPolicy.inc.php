<?php
/**
 * @file classes/security/authorization/OhsPluginAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OhsPluginAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OJS's plugins.
 */

import('lib.pkp.classes.security.authorization.PolicySet');
import('lib.pkp.classes.security.authorization.internal.PluginRequiredPolicy');

class OhsPluginAccessPolicy extends PolicySet {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 */
	function OhsPluginAccessPolicy(&$request, &$args, $roleAssignments) {
		parent::PolicySet();

		// A valid plugin is required.
		$this->addPolicy(new PluginRequiredPolicy($request));

		//
		// Site administrator role
		//
		if (isset($roleAssignments[ROLE_ID_SITE_ADMIN])) {
			// Site admin have access to all plugins...
			$this->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SITE_ADMIN, $roleAssignments[ROLE_ID_SITE_ADMIN]));
		}
	}
}

?>
