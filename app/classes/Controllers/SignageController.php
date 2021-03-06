<?php

namespace Controllers;

class SignageController extends \Controllers\BaseController {

	public function get() {

		// Get the next event
		$event = $this->app->db->queryRow('SELECT * FROM events WHERE end_time > NOW() ORDER BY start_time ASC LIMIT 1');

		$this->addViewData(array(
			'event' => $event,
			'message' => $this->req->getQuery('message'),
		));
		$this->renderView('sign');
	}
}
