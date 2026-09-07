<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\DashboardService;
use App\Services\RequestService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;
    private RequestService $requestService;

    public function __construct(DashboardService $dashboardService, RequestService $requestService)
    {
        $this->middleware('auth');
        $this->middleware('internal_admin');
        
        // Dashboard monitoring dengan hak akses yang sudah disamakan:
        // - Semua admin internal (QA, Purchasing, PPC): Full Access
        $this->dashboardService = $dashboardService;
        $this->requestService = $requestService;
    }

    /**
     * Tampilkan dashboard monitoring Purchasing dengan daftar requests
     * Sesuai update hak akses: semua admin internal memiliki full access
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isFullAccess = $user->hasDashboardFullAccess(); // Semua admin internal memiliki full access
        
        // Get all requests untuk ditampilkan di dashboard
        $requests = $this->requestService->getAllRequests(
            $request->only(['supplier_id', 'status', 'date_from', 'date_to'])
        );

        $suppliers = Supplier::all();

        return view('admin.dashboard.index', compact(
            'requests',
            'suppliers',
            'isFullAccess'
        ));
    }

    /**
     * View kategori proyek not submitted
     */
    public function notSubmitted()
    {
        $classifiedProjects = $this->dashboardService->classifyProjects();
        $projects = $classifiedProjects['not_submitted']['projects'];

        return view('admin.dashboard.category', [
            'category' => 'not_submitted',
            'label' => $classifiedProjects['not_submitted']['label'],
            'projects' => $projects,
        ]);
    }

    /**
     * View kategori proyek in progress
     */
    public function inProgress()
    {
        $classifiedProjects = $this->dashboardService->classifyProjects();
        $projects = $classifiedProjects['in_progress']['projects'];

        return view('admin.dashboard.category', [
            'category' => 'in_progress',
            'label' => $classifiedProjects['in_progress']['label'],
            'projects' => $projects,
        ]);
    }

    /**
     * View kategori proyek completed
     */
    public function completed()
    {
        $classifiedProjects = $this->dashboardService->classifyProjects();
        $projects = $classifiedProjects['completed']['projects'];

        return view('admin.dashboard.category', [
            'category' => 'completed',
            'label' => $classifiedProjects['completed']['label'],
            'projects' => $projects,
        ]);
    }
}
