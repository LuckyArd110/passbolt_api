<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.0.0
 */
namespace App\Test\Lib;

use App\Authenticator\SessionIdentificationServiceInterface;
use App\Middleware\CsrfProtectionMiddleware;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Test\Factory\UserFactory;
use App\Test\Lib\Model\AvatarsModelTrait;
use App\Test\Lib\Model\GpgkeysModelTrait;
use App\Test\Lib\Model\PermissionsModelTrait;
use App\Test\Lib\Model\ProfilesModelTrait;
use App\Test\Lib\Model\ResourcesModelTrait;
use App\Test\Lib\Model\RolesModelTrait;
use App\Test\Lib\Model\SecretsModelTrait;
use App\Test\Lib\Model\UsersModelTrait;
use App\Test\Lib\Utility\ArrayTrait;
use App\Test\Lib\Utility\EntityTrait;
use App\Test\Lib\Utility\ErrorTrait;
use App\Test\Lib\Utility\JsonRequestTrait;
use App\Test\Lib\Utility\ObjectTrait;
use App\Utility\Application\FeaturePluginAwareTrait;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use App\Utility\UserAction;
use App\Utility\UuidFactory;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

abstract class AppIntegrationTestCase extends TestCase
{
    use ArrayTrait;
    use AvatarsModelTrait;
    use EntityTrait;
    use ErrorTrait;
    use FeaturePluginAwareTrait;
    use GpgkeysModelTrait;
    use IntegrationTestTrait;
    use JsonRequestTrait;
    use ObjectTrait;
    use PermissionsModelTrait;
    use ProfilesModelTrait;
    use ResourcesModelTrait;
    use RolesModelTrait;
    use ScenarioAwareTrait;
    use SecretsModelTrait;
    use UsersModelTrait;

    /**
     * Setup.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
        $this->enableCsrfToken();
        $this->loadRoutes();
        Configure::write('passbolt.plugins.log.enabled', false);
        Configure::write(CsrfProtectionMiddleware::PASSBOLT_SECURITY_CSRF_PROTECTION_ACTIVE_CONFIG, true);
        OpenPGPBackendFactory::reset();
        UserAction::destroy();
    }

    /**
     * Tear down
     */
    public function tearDown(): void
    {
        $this->clearPlugins();
        parent::tearDown();
    }

    /**
     * Authenticate as a user.
     *
     * @param string $userFirstName The user first name.
     * @return void
     */
    public function authenticateAs($userFirstName)
    {
        $data = [
            'id' => UuidFactory::uuid('user.id.' . $userFirstName),
            'username' => $userFirstName . '@passbolt.com',
            'profile' => [
                'first_name' => $userFirstName,
                'last_name' => 'testing',
            ],
            'role' => [
                'name' => Role::USER,
            ],
        ];
        if ($userFirstName === 'admin') {
            $data['role']['name'] = Role::ADMIN;
        }
        $this->session(['Auth' => $data]);
    }

    /**
     * @param User $user
     */
    public function logInAs(User $user)
    {
        $this->session(['Auth' => $user]);
    }

    /**
     * @return User
     * @throws \Exception
     */
    public function logInAsUser()
    {
        $user = UserFactory::make()->user()->persist();
        $this->logInAs($user);

        return $user;
    }

    /**
     * @return User
     * @throws \Exception
     */
    public function logInAsAdmin()
    {
        $user = UserFactory::make()->admin()->persist();
        $this->logInAs($user);

        return $user;
    }

    /**
     * Calling this method will remove the CSRF token from the request.
     *
     * @return void
     */
    public function disableCsrfToken()
    {
        $this->_csrfToken = false;
    }

    /**
     * Injects in the DIC an Session Indentification Interface with the provided ID.
     * In Session, will return the session ID
     * In JWT, will return the access token
     *
     * @param string $sessionId Session Id to mock
     * @return void
     */
    public function mockSessionId(string $sessionId)
    {
        $this->mockService(SessionIdentificationServiceInterface::class, function () use ($sessionId) {
            $stubSessionIdentifier = $this->createMock(SessionIdentificationServiceInterface::class);
            $stubSessionIdentifier->method('getSessionId')->willReturn($sessionId);

            return $stubSessionIdentifier;
        });
    }
}
