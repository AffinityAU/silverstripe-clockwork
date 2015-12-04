<?php
/**
 * Adds timing and query information from silverstripe to the request.
 * One might add more information here in the future. I could imagine
 * it being helpful to enumerate config data or caching or routing or
 * tap into the silverstripe session/input classes instead of using
 * the default phpDataSource.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 11.08.2014
 * @package clockwork
 */

namespace Clockwork\Support\Silverstripe;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use DB;
use Injector;

class SilverstripeDataSource extends DataSource
{
	public static $controller;

    /**
     * The entry-point. called by Clockwork itself.
     * @param Request $request
     * @return \Clockwork\Request\Request
     */
    function resolve(Request $request)
    {
        // Retrieve the timeline
        $timeline = Injector::inst()->get('ClockworkTimeline');
        $timeline->finalize();
        $request->timelineData = $timeline->toArray();

        // Retrieve the query log
        $db = DB::getConn();
        if ($db instanceof DatabaseProxy) {
            $request->databaseQueries = $db->getQueries();
        }

        // Retrieve the log
        $log = Injector::inst()->get('ClockworkLog');
        $request->log = $log->toArray();

		// Fill in the rest of the request - these may not be finalized yet when the PhpDataSource sees them
		$request->cookies = \Cookie::get_all();
		$request->sessionData = \Session::get_all();

		// Give some knowledge about the controller if we have it
		if (isset(self::$controller)) {
			$request->controller = self::$controller;
		}

		// TODO: routing

		return $request;
    }
}
