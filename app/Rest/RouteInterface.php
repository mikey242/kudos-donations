<?php

namespace Kudos\Rest;

interface RouteInterface {

	/**
	 * Get all associated routes as an array.
	 */
	public function get_routes(): array;

}