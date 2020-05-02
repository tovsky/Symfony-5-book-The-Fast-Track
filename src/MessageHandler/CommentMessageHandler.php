<?php


namespace App\MessageHandler;


use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SpamChecker
     */
    private $spamChecker;
    /**
     * @var CommentRepository
     */
    private $commentRepository;
    /**
     * @var MessageBusInterface
     */
    private $bus;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var WorkflowInterface
     */
    private $workflow;
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var string
     */
    private $adminEmail;

    public function __construct(
        EntityManagerInterface $em,
        SpamChecker $spamChecker,
        CommentRepository $commentRepository,
        MessageBusInterface $bus,
        WorkflowInterface $commentStateMachine,
        MailerInterface $mailer,
        string $adminEmail,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->spamChecker = $spamChecker;
        $this->commentRepository = $commentRepository;
        $this->bus = $bus;
        $this->workflow = $commentStateMachine;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());

        if (!$comment) {
            return;
        }

        if ($this->workflow->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            $transition = 'accept';

            if (2 === $score) {
                $transition = 'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }

            $this->workflow->apply($comment, $transition);
            $this->em->flush();

            $this->bus->dispatch($message);

            return;
         }

        if ($this->workflow->can($comment, 'publish') ||
            $this->workflow->can($comment, 'publish_ham')
        ) {
//            $this->workflow->apply(
//                $comment,
//                $this->workflow->can($comment, 'publish') ? 'publish' : 'publish_ham'
//            );
//            $this->entityManager->flush();

            $this->mailer->send((new NotificationEmail())
                ->subject('New comment posted')
                ->htmlTemplate('emails/comment_notification.html.twig')
                ->from($this->adminEmail)
                ->to($this->adminEmail)
                ->context(['comment' => $comment])
            );

            return;
        }

        if ($this->logger) {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }

        $this->em->flush();
    }
}