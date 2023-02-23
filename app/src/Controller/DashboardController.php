<?php

namespace App\Controller;

use App\Berichtsheft\Heatmap;
use App\Entity\Apprenticeship;
use App\Entity\Entry;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\UuidV4;


class DashboardController extends AbstractController
{
    
    #[Route('/dashboard/add', name: 'app_dashboard_add')]
    public function dashboardAdd(
        Request $request,
        EntityManagerInterface $em,
        UserInterface $user
    ): Response {
        if (in_array('ROLE_AZUBI', $user->getRoles(), true)) {
            return $this->dashboardAddAzubi($em, $user, $request);
        }
        
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true)) {
            return $this->dashboardAddAusbilder($em, $user, $request);
        }
        
        throw new AccessDeniedHttpException();
    }
    
    private function dashboardAddAzubi(
        EntityManagerInterface $em,
        UserInterface $user,
        Request $request,
    ): Response {
        $form = $this->createFormBuilder()
            ->add('invite_token', TextType::class, [
                'label'    => 'Token',
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ausbildungstoken einlösen',
            ])
            ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $apprenticeship = $em->getRepository(Apprenticeship::class)->findOneBy([
                'inviteToken' => $request->get('form')['invite_token'],
            ]);
            
            if ($apprenticeship === null) {
                $this->addFlash('error', 'Ungültiger Token');
                
                $formView = $form->createView();
                return $this->render('dashboard/dashboardAddAzubi.html.twig', [
                    'form' => $formView,
                ]);
            }
            
            if ($apprenticeship->getAzubiId() !== null) {
                $this->addFlash('error', 'Token wurde bereits eingelöst');
                
                $formView = $form->createView();
                return $this->render('dashboard/dashboardAddAzubi.html.twig', [
                    'form' => $formView,
                ]);
            }
            
            $azubi = $em->getRepository(User::class)->findOneBy([
                'username' => $user->getUserIdentifier(),
            ]);
            
            if ($azubi === null) {
                throw new RuntimeException();
            }
            
            $apprenticeship->setAzubiId($azubi->getId());
            $em->persist($apprenticeship);
            $em->flush();
            
            $this->addFlash('success', 'Ausbildung erfolgreich hinzugefügt');
            return $this->redirectToRoute('app_dashboard');
        }
        
        $formView = $form->createView();
        return $this->render('dashboard/dashboardAddAzubi.html.twig', [
            'form' => $formView,
        ]);
    }
    
    private function dashboardAddAusbilder(
        EntityManagerInterface $em,
        UserInterface $user,
        Request $request,
    ): Response {
        $form = $this->createFormBuilder()
            ->add('title', TextType::class, [
                'label'    => 'Titel:',
                'required' => true,
            ])
            ->add('company_name', TextType::class, [
                'label'    => 'Firmenname',
                'required' => true,
            ])
            ->add('start_apprenticeship', DateType::class, [
                'label'    => 'Startdatum',
                'widget'   => 'single_text',
                'required' => true,
            ])
            ->add('end_apprenticeship', DateType::class, [
                'label'    => 'Enddatum',
                'widget'   => 'single_text',
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ausbildung erstellen',
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $ausbilder = $em->getRepository(User::class)->findOneBy([
                'username' => $user->getUserIdentifier(),
            ]);
            
            $token = new UuidV4();
            $token = $token->toRfc4122();
            
            if ($ausbilder === null) {
                throw new RuntimeException();
            }
            
            $apprenticeship = new Apprenticeship();
            $apprenticeship->setTitle($request->get('form')['title']);
            $apprenticeship->setCompanyName($request->get('form')['company_name']);
            $apprenticeship->setStartApprenticeship(new DateTime($request->get('form')['start_apprenticeship']));
            $apprenticeship->setEndApprenticeship(new DateTime($request->get('form')['end_apprenticeship']));
            $apprenticeship->setAusbilderId($ausbilder->getId());
            $apprenticeship->setInviteToken($token);
            
            $em->persist($apprenticeship);
            $em->flush();
            $this->addFlash('success', "Ausbildung erfolgreich angelegt. Der Token lautet $token");
            
            return $this->redirectToRoute('app_dashboard');
        }
        
        $formView = $form->createView();
        return $this->render('dashboard/dashboardAddAzubi.html.twig', [
            'form' => $formView,
        ]);
    }
    
    #[Route('/dashboard/{id}', name: 'overview_dashboard')]
    public function overviewDashboard(int $id): Response
    {
        return new Response('1');
    }
    
    #[Route('/dashboard/{id}/{date}', name: 'report_dashboard')]
    public function dailyReport(
        EntityManagerInterface $em,
        int $id,
        DateTime $date,
    ): Response {
        $entries = $em->getRepository(Entry::class)->findBy([
            'apprenticeshipId' => $id,
            'date'             => $date,
        ]);
        $entries = array_map(function ($entry) {
            return (object)[
                'id'               => $entry->getId(),
                'apprenticeshipId' => $entry->getApprenticeshipId(),
                'date'             => $entry->getDate()->format('Y-m-d'),
                'time'             => $entry->getTime(),
                'text'             => $entry->getText(),
            ];
        }, $entries);
        return $this->render('dashboard/dashboardWeeklyReport.html.twig', [
            'entries'          => $entries,
            'apprenticeshipId' => $id,
            'date'             => $date->format('Y-m-d'),
        ]);
    }
    
    #[Route('/dashboard/{id}/{date}/deleteEntry/{entryId}', name: 'delete_entry')]
    public function deleteEntry(
        Request $request,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        int $id,
        DateTime $date,
        int $entryId,
    ) {
        $entry = $doctrine->getRepository(Entry::class)->findOneBy(
            ['id' => $entryId]
        );
        
        $form = $this->createFormBuilder()
            ->add('delete', CheckboxType::class, [
                'label'    => 'Ja',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Eintrag löschen',])
            ->getForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('form')['delete']) {
                $em->remove($entry);
                $em->flush();
                $this->addFlash(
                    'success',
                    'Eintrag wurde erfolgreich gelöscht'
                );
            } else {
                $this->addFlash('info', 'Eintrag wurde nicht gelöscht');
            }
            return $this->redirectToRoute('report_dashboard', [
                'id'   => $id,
                'date' => $date->format('Y-m-d'),
            ]);
        }
        
        $formView = $form->createView();
        return $this->render('dashboard/dashboardDeleteEntry.html.twig', [
            'form' => $formView,
        ]);
    }
    
    #[Route('/dashboard/{id}/{date}/add', name: 'add_entry')]
    public function addEntry
    (
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        int $id,
        DateTime $date,
        Request $request,
    ): Response {
        $entry = new Entry();
        $form  = $this->entryForm($entry, DashboardEntrySubmitType::CREATE);
        $form->handleRequest($request);
        
        
        $apprenticeship = $em->getRepository(Apprenticeship::class)->findOneBy([
            'id' => $id,
        ]);
        
        if ($form->isSubmitted()) {
            $entry = new Entry();
            $entry->setApprenticeshipId($apprenticeship);
            $entry->setText($request->get('form')['text']);
            $entry->setTime($request->get('form')['time']);
            $entry->setDate($date);
            
            $em->persist($entry);
            $em->flush();
            return $this->redirectToRoute('report_dashboard', [
                'id'   => $id,
                'date' => $date->format('Y-m-d'),
            ]);
        }
        $formView = $form->createView();
        return $this->render('dashboard/dashboardCreateNewReport.html.twig', [
            'form' => $form,
        ]);
    }
    
    public function entryForm(Entry $entry, DashboardEntrySubmitType $type): FormInterface
    {
        return $this->createFormBuilder($entry)
            ->add('text', TextType::class, [
                'label'    => 'text',
                'required' => true,
            ])
            ->add('time', NumberType::class, [
                'label'    => 'Vorgangsdauer',
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => match ($type) {
                    DashboardEntrySubmitType::CREATE => 'Eintrag erstellten',
                    DashboardEntrySubmitType::EDIT => 'Eintrag bearbeiten',
                },
            ])
            ->getForm();
    }
    
    #[Route('/dashboard/{id}/{date}/edit/{entryid}', name: 'edit_entry')]
    public function editEntry
    (
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        int $id,
        int $entryid,
        DateTime $date,
        Request $request,
    ): Response {
        $entry = $doctrine->getRepository(Entry::class)->findOneBy(
            ['id' => $entryid]
        );
        
        $form = $this->entryForm($entry, DashboardEntrySubmitType::EDIT);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $form->getData();
            
            $em->persist($entry);
            $em->flush();
            return $this->redirectToRoute('report_dashboard', [
                'id'   => $id,
                'date' => $date->format('Y-m-d'),
            ]);
        }
        $formView = $form->createView();
        return $this->render('dashboard/dashboardCreateNewReport.html.twig', [
            'form' => $form,
        ]);
    }
    
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        EntityManagerInterface $em,
        UserInterface $user
    ): Response {
        $userObj = $em->getRepository(User::class)->findOneby([
            'username' => $user->getUserIdentifier(),
        ]);
        
        if ($userObj === null) {
            throw new AccessDeniedException();
        }
        
        if (in_array('ROLE_AZUBI', $user->getRoles(), true)) {
            $apprenticeships = $em->getRepository(Apprenticeship::class)->findBy([
                'azubiId' => $userObj->getId(),
            ]);
            
            $targetArray = [];
            
            foreach ($apprenticeships as $apprenticeship) {
                $ausbilder = $em->getRepository(User::class)->find($apprenticeship->getAusbilderId());
                
                $entries = Heatmap::handleAzubi(
                    $apprenticeship->getStartApprenticeship(),
                    $apprenticeship->getEndApprenticeship(),
                    $apprenticeship->getEntries()->toArray(),
                );
                
                $unread = array_filter($entries, static function ($entry) {
                    return $entry->value !== 14;
                });
                
                $targetArray[] = (object)[
                    'id'        => $apprenticeship->getId(),
                    'title'     => $apprenticeship->getTitle(),
                    'firstname' => $ausbilder?->getFirstname(),
                    'lastname'  => $ausbilder?->getLastname(),
                    'unread'    => count($unread),
                    'start'     => $apprenticeship->getStartApprenticeship()?->format('Y-m-d'),
                    'end'       => $apprenticeship->getEndApprenticeship()?->format('Y-m-d'),
                    'entries'   => $entries,
                ];
            }
            
            return $this->render('dashboard/dashboardAzubi.html.twig', [
                'apprenticeships' => $targetArray,
            ]);
        }
        
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true)) {
            $apprenticeships = $em->getRepository(Apprenticeship::class)->findBy([
                'ausbilderId' => $userObj->getId(),
            ]);
            
            $targetArray = [];
            
            foreach ($apprenticeships as $apprenticeship) {
                $azubi = null;
                
                if ($apprenticeship->getAzubiId() !== null) {
                    $azubi = $em->getRepository(User::class)->find($apprenticeship->getAzubiId());
                }
                
                $entries = Heatmap::handleAzubi(
                    $apprenticeship->getStartApprenticeship(),
                    $apprenticeship->getEndApprenticeship(),
                    $apprenticeship->getEntries()->toArray(),
                );
                
                $unread = array_filter($entries, static function ($entry) {
                    return $entry->value === 22;
                });
                
                $targetArray[] = (object)[
                    'id'        => $apprenticeship->getId(),
                    'azubiId'   => $azubi?->getId(),
                    'firstname' => $azubi?->getFirstname(),
                    'lastname'  => $azubi?->getLastname(),
                    'unread'    => count($unread),
                    'token'     => $apprenticeship->getInviteToken(),
                    'start'     => $apprenticeship->getStartApprenticeship()?->format('Y-m-d'),
                    'end'       => $apprenticeship->getEndApprenticeship()?->format('Y-m-d'),
                    'entries'   => $entries,
                ];
            }
            
            return $this->render('dashboard/dashboardAusbilder.html.twig', [
                'apprenticeships' => $targetArray,
            ]);
        }
        
        return $this->redirectToRoute('app_login');
    }
    
    #[Route('/dashboard/{apprenticeshipId}')]
    public function dashboardApprenticeship(
        UserInterface $user,
        int $apprenticeshipId
    ): Response {
        if (in_array('ROLE_AZUBI', $user->getRoles(), true)) {
            return $this->apprenticeshipAzubi($apprenticeshipId);
        }
        
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true)) {
            return $this->apprenticeshipAusbilder($apprenticeshipId);
        }
        
        throw new AccessDeniedHttpException();
    }
    
    private function apprenticeshipAzubi(
        int $apprenticeshipId
    ): Response {
        return new Response("Work in progress. AZUBI ON $apprenticeshipId");
    }
    
    private function apprenticeshipAusbilder(
        int $apprenticeshipId
    ): Response {
        return new Response("Work in progress. AUSBILDER ON $apprenticeshipId");
    }
    
    #[Route('/dashboard/{apprenticeshipId}/{date}/accept/${entryId}', name: 'app_dashboard_dashboardviewday_accept')]
    public function dashboardViewDayAccept(
        EntityManagerInterface $em,
        UserInterface $user,
        int $entryId,
        int $apprenticeshipId,
        DateTime $date,
    ): Response {
        $entry = $em->getRepository(Entry::class)->find($entryId) ??
            throw new RuntimeException();
        
        $apprenticeship = $entry->getApprenticeshipId() ?? throw new RuntimeException();
        
        $ausbilderId = $em->getRepository(User::class)->findOneBy([
            'username' => $user->getUserIdentifier(),
        ])?->getId() ?? throw new RuntimeException();
        
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true) && $apprenticeship->getAusbilderId() === $ausbilderId) {
            $entry->setStatus(1);
            $em->persist($entry);
            $em->flush();
            $this->addFlash('success', 'Eintrag erfolgreich abgesegnet.');
        } else {
            throw new AccessDeniedHttpException();
        }
        
        return $this->redirectToRoute('app_dashboard_dashboardviewday', [
            'apprenticeshipId' => $apprenticeshipId,
            'date'             => $date->format('Y-m-d'),
        ]);
    }
    
    #[Route('/dashboard/{apprenticeshipId}/{date}/deny/${entryId}', name: 'app_dashboard_dashboardviewday_deny')]
    public function dashboardViewDayDeny(
        EntityManagerInterface $em,
        UserInterface $user,
        int $entryId,
        int $apprenticeshipId,
        DateTime $date,
    ): Response {
        $entry = $em->getRepository(Entry::class)->find($entryId) ??
            throw new RuntimeException();
        
        $apprenticeship = $entry->getApprenticeshipId() ?? throw new RuntimeException();
        
        $ausbilderId = $em->getRepository(User::class)->findOneBy([
            'username' => $user->getUserIdentifier(),
        ])?->getId() ?? throw new RuntimeException();
        
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true) && $apprenticeship->getAusbilderId() === $ausbilderId) {
            $entry->setStatus(2);
            $em->persist($entry);
            $em->flush();
            $this->addFlash('success', 'Eintrag erfolgreich abgesegnet.');
        } else {
            throw new AccessDeniedHttpException();
        }
        
        return $this->redirectToRoute('app_dashboard_dashboardviewday', [
            'apprenticeshipId' => $apprenticeshipId,
            'date'             => $date->format('Y-m-d'),
        ]);
    }
    
    #[Route('/dashboard/{apprenticeshipId}/{date}', name: 'app_dashboard_dashboardviewday')]
    public function dashboardViewDay(
        EntityManagerInterface $em,
        UserInterface $user,
        int $apprenticeshipId,
        DateTime $date,
    ): Response {
        if (in_array('ROLE_AZUBI', $user->getRoles(), true)) {
            return $this->apprenticeshipAzubiDay($apprenticeshipId);
        }
        
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true)) {
            return $this->apprenticeshipAusbilderDay(
                $em,
                $apprenticeshipId,
                $date
            );
        }
        
        throw new AccessDeniedHttpException();
    }
    
    private function apprenticeshipAzubiDay(
        int $apprenticeshipId,
    ): Response {
        return new Response("Work in progress. AUSBILDER ON $apprenticeshipId");
    }
    
    private function apprenticeshipAusbilderDay(
        EntityManagerInterface $em,
        int $apprenticeshipId,
        DateTime $date,
    ): Response {
        $currentDate = $date->format('Y-m-d');
        
        $apprenticeship = $em->getRepository(Apprenticeship::class)->find($apprenticeshipId) ??
            throw new RuntimeException();
        
        $entries = $em->getRepository(Entry::class)->findBy([
            'apprenticeshipId' => $apprenticeshipId,
            'date'             => $date,
        ]);
        
        $azubi = $em->getRepository(User::class)->find($apprenticeship->getAzubiId()) ??
            throw new RuntimeException();
        
        $calendar = Heatmap::handleAzubi(
            $apprenticeship->getStartApprenticeship(),
            $apprenticeship->getEndApprenticeship(),
            $apprenticeship->getEntries()->toArray(),
        );
        
        return $this->render('dashboard/dashboardAusbilderDay.html.twig', [
            'currentDate'    => $currentDate,
            'user'           => $azubi,
            'entries'        => $entries,
            'date'           => $date->format('Y-m-d'),
            'apprenticeship' => (object)[
                'id'    => $apprenticeship->getId(),
                'start' => $apprenticeship->getStartApprenticeship()?->format('Y-m-d'),
                'end'   => $apprenticeship->getEndApprenticeship()?->format('Y-m-d'),
            ],
            'calendar'       => $calendar,
        ]);
    }
    
    
}

enum DashboardEntrySubmitType
{
    
    case CREATE;
    case EDIT;
    
}
