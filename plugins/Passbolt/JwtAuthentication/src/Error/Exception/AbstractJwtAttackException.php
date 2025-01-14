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

namespace Passbolt\JwtAuthentication\Error\Exception;

use App\Error\Exception\AbstractExceptionWithEmailEvent;
use App\Error\Exception\AdminsEmailNotificationExceptionTrait;
use App\Error\Exception\UserEmailNotificationExceptionTrait;
use Passbolt\JwtAuthentication\Error\Exception\RefreshToken\RefreshTokenNotFoundException;
use Passbolt\JwtAuthentication\Service\RefreshToken\RefreshTokenAbstractService;
use Passbolt\JwtAuthentication\Service\RefreshToken\RefreshTokenFetchUserService;

/**
 * Abstract class for all JWT attack related exceptions.
 */
abstract class AbstractJwtAttackException extends AbstractExceptionWithEmailEvent implements
    UserEmailNotificationExceptionTrait,
    AdminsEmailNotificationExceptionTrait
{
    public const USER_EMAIL_SUBJECT = 'Authentication security alert!';
    public const ADMIN_EMAIL_SUBJECT = 'Authentication security alert!';

    /**
     * @inheritDoc
     */
    public function getEventName(): string
    {
        return static::class;
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): ?string
    {
        // Get the user ID from the payload
        $request = $this->getController()->getRequest();
        if ($request->getData('user_id')) {
            return $request->getData('user_id');
        }

        // Get the user ID from the refresh token in cookie
        $token = $request->getCookie(RefreshTokenAbstractService::REFRESH_TOKEN_COOKIE);
        if (!empty($token)) {
            try {
                $userId = (new RefreshTokenFetchUserService($token))->getUserIdFromToken();
            } catch (RefreshTokenNotFoundException $e) {
                $userId = null;
            }
        }
        if (!empty($userId)) {
            return $userId;
        }

        return $this->getController()->User->id();
    }

    /**
     * @inheritDoc
     */
    public function getUserEmailTemplate(): string
    {
        return 'JwtAuthentication.User/jwt_attack';
    }

    /**
     * @inheritDoc
     */
    public function getAdminEmailTemplate(): string
    {
        return 'JwtAuthentication.Admin/jwt_attack';
    }

    /**
     * @inheritDoc
     */
    public function getUserEmailSubject(): string
    {
        return self::USER_EMAIL_SUBJECT;
    }

    /**
     * @inheritDoc
     */
    public function getAdminEmailSubject(): string
    {
        return self::ADMIN_EMAIL_SUBJECT;
    }
}
