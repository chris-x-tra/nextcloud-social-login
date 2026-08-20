<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2021 Hybridauth authors | https://hybridauth.github.io/license.html
*
* Authelia Class by chrissie ^ x-tra-designs done 08.2024
*
*/

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\InvalidApplicationCredentialsException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;


/**
 * Authelia OpenId Connect provider adapter.
 *
 * Example:
 *         'Authelia' => [
 *             'enabled' => true,
 *             'url' => 'https://secure.mydomain.de', 		// Authelia URL, no need to add /api/oidc/
 *             'realm' => 'your-realm',				// not needed
 *             'keys' => [
 *                 'id' => 'nextcloud',
 *                 'secret' => 'OdZl7194.......Bq1LrNntQT1PtcjwLCrFxBglevs.......88Z2vA0lnDSqmTS'
 *             ]
 *         ]
 *
 */
class Authelia extends OAuth2
{

    /**
     * {@inheritdoc}
     */
    public $scope = 'openid profile email';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://github.com/authelia/authelia/blob/master/api/openapi.yml';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

// TODO: this must be able to set in nextcloud sociallogin settings
$url = 'https://secure.mydomain.de';
/*
        if (!$this->config->exists('url')) {
            throw new InvalidApplicationCredentialsException(
                'You must define a provider url'
            );
        }
        $url = $this->config->get('url');

        if (!$this->config->exists('realm')) {
            throw new InvalidApplicationCredentialsException(
                'You must define a realm'
            );
        }
        $realm = $this->config->get('realm');
*/

        $this->apiBaseUrl = $url . '/api/oidc/';

        $this->authorizeUrl = $this->apiBaseUrl . 'authorization';
        $this->accessTokenUrl = $this->apiBaseUrl . 'token';

    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('userinfo');

        $data = new Data\Collection($response);

        if (!$data->exists('sub')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('sub');
        $userProfile->displayName = $data->get('preferred_username');
        $userProfile->email = $data->get('email');
        $userProfile->firstName = $data->get('given_name');
        $userProfile->lastName = $data->get('family_name');
        $userProfile->emailVerified = $data->get('email_verified');

        return $userProfile;
    }
}
