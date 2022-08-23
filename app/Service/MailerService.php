<?php

namespace Kudos\Service;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Assets;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\Vendor\MollieVendor;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;
use WP_REST_Request;

class MailerService
{
    /**
     * From header
     *
     * @var bool|mixed|void
     */
    private $from;
    /**
     * @var TwigService
     */
    private $twig;
    /**
     * @var MapperService
     */
    private $mapper;
    /**
     * @var \Kudos\Service\LoggerService
     */
    private $logger;
    /**
     * @var mixed
     */
    private $custom_config;
    /**
     * @var mixed
     */
    private $custom_smtp;
    /**
     * @var mixed
     */
    private $bcc;

    /**
     * Mailer constructor.
     */
    public function __construct(TwigService $twig, MapperService $mapper, LoggerService $logger)
    {
        $this->twig        = $twig;
        $this->mapper      = $mapper;
        $this->logger      = $logger;
        $this->custom_smtp = Settings::get_setting('smtp_enable');
        $this->bcc         = filter_var(Settings::get_setting('email_bcc'), FILTER_SANITIZE_EMAIL);
        if ($this->custom_smtp) {
            $this->custom_config = Settings::get_setting('custom_smtp');
        }
    }

    /**
     * Initializes the mailer by modifying default config if setting
     * is enabled.
     *
     * @param PHPMailer $phpmailer PHPMailer instance.
     *
     * @throws Exception From PHPMailer.
     */
    public function init(PHPMailer $phpmailer)
    {
        // Toggle this on to enable PHPMailer's debug mode.
        $phpmailer->SMTPDebug = 0;

        // Set higher timeout.
        $phpmailer->Timeout = 10;

        // Add logo as attachment.
        $phpmailer->addEmbeddedImage(
            Assets::get_asset_path('images/logo-colour-40.png'),
            'kudos-logo',
            'kudos-logo.png'
        );

        // Enable HTML email support.
        $phpmailer->isHTML();

        // Add BCC.
        if ($this->bcc) {
            $phpmailer->addBCC($this->bcc);
        }

        // Add custom config if enabled.
        if ($this->custom_smtp) {
            $phpmailer->isSMTP();
            $phpmailer->Host = $this->custom_config['host'];
//            $phpmailer->SMTPAutoTLS = $this->custom_config['autotls'];
            $phpmailer->SMTPAuth = true;
            if ('none' !== $this->custom_config['encryption']) {
                $phpmailer->SMTPSecure = $this->custom_config['encryption'];
            }
            $phpmailer->Username = $this->custom_config['username'];
            $phpmailer->Password = $this->custom_config['password'];
            $phpmailer->Port     = $this->custom_config['port'];

            $phpmailer->From     = $this->custom_config['from_email'];
            $phpmailer->FromName = $this->custom_config['from_name'];
        }
    }

    /**
     * Sends receipt to the donor.
     *
     * @param TransactionEntity $transaction TransactionEntity object.
     *
     * @return bool
     */
    public function send_receipt(TransactionEntity $transaction): bool
    {
        // Check if setting enabled.
        if ( ! Settings::get_setting('email_receipt_enable')) {
            return false;
        }

        // Assign attachment.
        $attachments = apply_filters('kudos_receipt_attachment', [], $transaction->order_id);

        // Get donor details.
        /** @var DonorEntity $donor */
        $donor = $this->mapper
            ->get_repository(DonorEntity::class)
            ->get_one_by(['customer_id' => $transaction->customer_id]);

        // Create array of variables for use in twig template.
        $render_array = [
            'name'         => $donor->name ?? '',
            'date'         => $transaction->created,
            'description'  => MollieVendor::get_sequence_type($transaction->sequence_type),
            'amount'       => (! empty($transaction->currency) ? html_entity_decode(
                    Utils::get_currency_symbol($transaction->currency)
                ) : '') . number_format_i18n(
                                  $transaction->value,
                                  2
                              ),
            'receipt_id'   => $transaction->order_id,
            'website_name' => get_bloginfo('name'),
        ];

        // Add a cancel subscription url if transaction associated with a subscription.
        if ( ! empty($transaction->subscription_id)) {
            $mapper          = $this->mapper;
            $subscription_id = $transaction->subscription_id;
            /** @var SubscriptionEntity $subscription */
            $subscription               = $mapper
                ->get_repository(SubscriptionEntity::class)
                ->get_one_by(['subscription_id' => $subscription_id]);
            $action                     = 'cancel_subscription';
            $cancel_url                 = add_query_arg(
                [
                    'kudos_action'          => $action,
                    'kudos_nonce'           => wp_create_nonce($action),
                    'kudos_subscription_id' => $subscription_id,
                ],
                get_home_url()
            );
            $render_array['cancel_url'] = $cancel_url;
            $mapper->save($subscription);
        }

        $twig = $this->twig;
        $body = $twig->render('emails/receipt.html.twig', $render_array);

        return $this->send(
            $donor->email,
            __('Donation Receipt', 'kudos-donations'),
            $body,
            $attachments
        );
    }

    /**
     * Email send function.
     *
     * @param string $to Recipient email address.
     * @param string $subject Email subject line.
     * @param string $body Body of email.
     * @param array|null $attachment Attachment.
     *
     * @return bool
     */
    private function send(
        string $to,
        string $subject,
        string $body,
        array $attachment = []
    ): bool {
        // Use hook to modify existing config.
        add_action('phpmailer_init', [$this, 'init']);
        add_action('wp_mail_failed', [$this, 'log_error']);

        $mail = wp_mail($to, $subject, $body, '', $attachment);

        if ($mail) {
            $this->logger->info('Email sent successfully.', ['to' => $to, 'subject' => $subject]);
        }

        remove_action('phpmailer_init', [$this, 'init']);
        remove_action('wp_mail_failed', [$this, 'log_error']);

        return $mail;
    }

    /**
     * Sends a test email using send_message.
     *
     * @param WP_REST_Request $request Request array.
     *
     * @return bool
     */
    public function send_test(WP_REST_Request $request): bool
    {
        if (empty($request['email'])) {
            wp_send_json_error(__('Please provide an email address.', 'kudos-donations'));
        }

        $email   = sanitize_email($request['email']);
        $header  = __('It worked!', 'kudos-donations');
        $message = __('Looks like your email settings are set up correctly :-)', 'kudos-donations');

        $result = $this->send_message($email, $header, $message);

        if ($result) {
            /* translators: %s: API mode */
            wp_send_json_success(sprintf(__('Email sent to %s.', 'kudos-donations'), $email));
        } else {
            /* translators: %s: API mode */
            wp_send_json_error(
                __(
                    'Error sending email, please check the settings and try again.',
                    'kudos-donations'
                )
            );
        }

        return $result;
    }

    /**
     * Sends a message using the message template
     *
     * @param string $email Email address.
     * @param string $header Email headers.
     * @param string $message Email body.
     *
     * @return bool
     */
    public function send_message(string $email, string $header, string $message): bool
    {
        $twig = $this->twig;
        $body = $twig->render(
            'emails/message.html.twig',
            [
                'header'       => $header,
                'message'      => $message,
                'website_name' => get_bloginfo('name'),
            ]
        );

        return $this->send($email, $header, $body);
    }

    /**
     * Logs the supplied WP_Error object.
     *
     * @param WP_Error $error
     */
    public function log_error(WP_Error $error)
    {
        $this->logger->error('Error sending email.', $error->errors);
    }
}
