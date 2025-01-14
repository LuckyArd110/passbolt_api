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
 * @since         3.3.0
 */

namespace Passbolt\JwtAuthentication\Test\TestCase\Service\RefreshToken;

use App\Model\Entity\AuthenticationToken;
use App\Test\Factory\AuthenticationTokenFactory;
use Cake\Datasource\ModelAwareTrait;
use Cake\TestSuite\TestCase;
use Passbolt\JwtAuthentication\Error\Exception\RefreshToken\RefreshTokenNotFoundException;
use Passbolt\JwtAuthentication\Service\RefreshToken\RefreshTokenFetchUserService;

/**
 * @covers \Passbolt\JwtAuthentication\Service\RefreshToken\RefreshTokenRenewalService
 * @property \App\Model\Table\AuthenticationTokensTable $AuthenticationTokens
 */
class RefreshTokenFetchUserServiceTest extends TestCase
{
    use ModelAwareTrait;

    public function setUp(): void
    {
        $this->loadModel('AuthenticationTokens');
    }

    public function testRefreshTokenFetchUserService_getUserIdFromToken_Success()
    {
        $refreshToken = AuthenticationTokenFactory::make()
            ->active()
            ->type(AuthenticationToken::TYPE_REFRESH_TOKEN)
            ->persist();

        $service = (new RefreshTokenFetchUserService($refreshToken->token));
        $this->assertSame($refreshToken->user_id, $service->getUserIdFromToken());
    }

    /**
     * No users are found for the refresh token type.
     */
    public function testRefreshTokenFetchUserService_getUserIdFromToken_No_User_Found()
    {
        $refreshToken = AuthenticationTokenFactory::make()
            ->active()
            ->type(AuthenticationToken::TYPE_RECOVER)
            ->persist();

        $this->expectException(RefreshTokenNotFoundException::class);
        $this->expectExceptionMessage('No active refresh token matching the request could be found.');

        (new RefreshTokenFetchUserService($refreshToken->token))->getUserIdFromToken();
    }
}
