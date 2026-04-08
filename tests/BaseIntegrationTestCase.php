<?php

namespace IseardMedia\Kudos\Tests;

/**
 * Base class for integration tests.
 *
 * Extend this for any test in tests/Integration/ that needs the full WordPress
 * + plugin environment. Shared integration-test setup (e.g. disabling Action
 * Scheduler, configuring Mollie test mode) can be added here over time.
 */
abstract class BaseIntegrationTestCase extends BaseTestCase {}
