<?php
/**
 * Imagine if we could subscribe to URLs....
 *
 *  You, you may say. I'm a dreamer,
 *  but I'm not the only one.
 *
 */
interface Ban_Service_PubsubInterface
{
    public function subscribe(Ban_Request $request);
    public function unsubscribe(Ban_Request $request);
}
