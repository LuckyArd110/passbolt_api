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
namespace App\Test\Lib\Model;

use Cake\Core\Configure;
use Passbolt\EmailDigest\Test\Factory\EmailQueueFactory;
use Passbolt\EmailNotificationSettings\Utility\EmailNotificationSettings;

trait EmailQueueTrait
{
    /**
     * @var array
     */
    protected $backupEmailNotificationSettings = [];

    /**
     * Asserts that an email with given properties is in the email queue.
     */
    protected function assertEmailIsInQueue(array $properties)
    {
        $this->assertTrue(EmailQueueFactory::count() > 0, 'The email is not in the email queue.');
    }

    /**
     * Asserts that an email with given recipient is in the email queue.
     */
    protected function assertEmailWithRecipientIsInQueue(string $email)
    {
        $this->assertEmailIsInQueue(compact('email'));
    }

    /**
     * Asserts that n emails are in the email queue.
     */
    protected function assertEmailQueueCount(int $n)
    {
        $this->assertSame($n, EmailQueueFactory::find()->count());
    }

    /**
     * Asserts that no email is in the email queue.
     */
    protected function assertEmailQueueIsEmpty()
    {
        $this->assertEmailQueueCount(0);
    }

    /**
     * Not all email notifications are activated per default.
     * Particularly on the cloud.
     * This method enables the activation of the provided email notification setting
     *
     * @param string $notificationSettingPath Notification path
     * @param bool $value Value to assign
     * @return void
     */
    protected function setEmailNotificationsSetting(string $notificationSettingPath, bool $value): void
    {
        EmailNotificationSettings::flushCache();
        $this->backupEmailNotificationSettings = array_merge_recursive(
            Configure::read('passbolt.email.send'),
            $this->backupEmailNotificationSettings
        );
        Configure::write('passbolt.email.send.' . $notificationSettingPath, $value);
    }

    /**
     * Whenever an email notification setting is set with setEmailNotificationsSetting, this method will restore the
     * settings to settings value prior to the test. This is necessary to ensute test independence
     */
    protected function restoreEmailNotificationsSettings(): void
    {
        Configure::write('passbolt.email.send', $this->backupEmailNotificationSettings);
        EmailNotificationSettings::flushCache();
    }

    /**
     * Asserts that all emails of a given recipient have a locale set and equal to the expectation.
     *
     * @param string $email Recipient
     * @param string $expectedLocale Expected locale
     */
    protected function assetEmailLocale(string $email, string $expectedLocale)
    {
        $emails = EmailQueueFactory::find()->where(compact('email'));
        $this->assertTrue($emails->count() > 0);
        foreach ($emails as $email) {
            $this->assertTextEquals($expectedLocale, $email->get('template_vars')['locale']);
        }
    }

    /**
     * Asserts that all emails of a given recipient have expected subject.
     *
     * @param string $email Recipient
     * @param string $expectedSubject Expected subject
     */
    protected function assetEmailSubject(string $email, string $expectedSubject)
    {
        $emails = EmailQueueFactory::find()->where(compact('email'));
        $this->assertTrue($emails->count() > 0);
        foreach ($emails as $email) {
            $this->assertTextEquals($expectedSubject, $email->get('subject'));
        }
    }
}
