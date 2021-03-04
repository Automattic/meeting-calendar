<?php
use WordPressdotorg\Meeting_Calendar;

/**
 * Class MeetingiCalTest
 *
 * @package Meeting_Calendar
 */

/**
 * Sample test case.
 */
class MeetingiCalTest extends WP_UnitTestCase {
	protected $server;
	protected $meeting_ids;

	function setUp() {
		parent::setUp();

		// Initialize a REST server
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server();
		do_action( 'rest_api_init' );

		// Install test data
		$this->meeting_ids = Meeting_Calendar\wporg_meeting_install();

		// Make sure the meta keys are registered - setUp/tearDown nukes these
		Meeting_Post_Type::getInstance()->register_meta();
	}

	public function test_get_ical() {
		$posts = WordPressdotorg\Meeting_Calendar\ICS\get_meeting_posts();
		Meeting_Post_Type::getInstance()->meeting_set_next_meeting(
			$posts,
			new WP_Query(
				array(
					'post_type' => 'meeting',
					'nopaging'  => true,
				)
			)
		);

		$ical_feed  = WordPressdotorg\Meeting_Calendar\ICS\Generator\generate( $posts );
		$events_ics = file_get_contents( __DIR__ . '/fixtures/events.ics' );
		$events_ics = str_replace( '%ID1%', str_replace( '-', '', $posts[0]->ID ), $events_ics );
		$events_ics = str_replace( '%ID2%', str_replace( '-', '', $posts[1]->ID ), $events_ics );
		$events_ics = str_replace( '%ID3%', str_replace( '-', '', $posts[2]->ID ), $events_ics );

		$this->assertEquals(
			preg_split( '/\r\n|\r|\n/', $events_ics ),
			preg_split( '/\r\n|\r|\n/', $ical_feed )
		);
	}

	public function test_get_ical_with_cancellation() {
		$posts = WordPressdotorg\Meeting_Calendar\ICS\get_meeting_posts( 'Team-A' );
		// Cancel the second occurrence of the weekly meeting
		$occurrences = Meeting_Post_Type::getInstance()->get_future_occurrences( get_post( $posts[0]->ID ), null, null );
		$this->assertGreaterThan(
			0,
			Meeting_Post_Type::getInstance()->cancel_meeting(
				array(
					'meeting_id' => $posts[0]->ID,
					'date'       => $occurrences[1],
				)
			)
		);

		Meeting_Post_Type::getInstance()->meeting_set_next_meeting(
			$posts,
			new WP_Query(
				array(
					'post_type' => 'meeting',
					'nopaging'  => true,
				)
			)
		);

		$ical_feed  = WordPressdotorg\Meeting_Calendar\ICS\Generator\generate( $posts );
		$events_ics = file_get_contents( __DIR__ . '/fixtures/events-with-cancel.ics' );
		$events_ics = str_replace( '%ID%', str_replace( '-', '', $posts[0]->ID ), $events_ics );
		$events_ics = str_replace( '%EXDATE%', str_replace( '-', '', $occurrences[1] ), $events_ics );

		$this->assertEquals(
			preg_split( '/\r\n|\r|\n/', $events_ics ),
			preg_split( '/\r\n|\r|\n/', $ical_feed )
		);
	}
}
