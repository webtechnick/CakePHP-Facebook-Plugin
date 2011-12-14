<?php
App::import('Lib', 'Facebook.FacebookApi');

/**
 * Shareable Behavior
 *
 * @package facebook
 * @subpackage facebook.models.behaviors
 */
class ShareableBehavior extends ModelBehavior
{

    /**
     * Setup.
     *
     * @access public
     * @param Object $Model
     * @param array $config
     */
    function setup(&$Model, $config = array())
    {
        $this->FB = new FacebookApi();
    }

    /**
     * Share messages as an user.
     *
     * @access public
     * @param mixed $user
     * @param string $text
     * @return boolean
     */
    function share(&$Model, $user, $text, $options = array())
    {
        $defaults = array('url' => FULL_BASE_URL);
        $options = array_merge($defaults, $options);

        if (!is_array($user) || !array_key_exists($Model->alias, $user)) {
            $params = array(
                'conditions' => array(
                    $Model->alias . '.id = ' => $user,
                ),
                'fields' => array(
                    $Model->alias . '.facebook_id',
                ),
                'recursive' => -1,
            );
            $user = $Model->find('first', $params);
        }
        $profileID = $user['User']['facebook_id'];
        $method = '/' . $profileID . '/feed';
        $params = array(
            'message' => $text,
            'link' => $options['url'],
        );
        if (Configure::read('debug') < 2) {
            return $this->Api->FB->api($method, 'POST', $params);
        } else {
            return $Model->log($params, LOG_NOTICE);
        }
    }

}
