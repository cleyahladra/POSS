<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/CustomerModel.php';

class CustomersController extends Controller {
    private CustomerModel $customerModel;

    public function __construct() {
        $this->customerModel = new CustomerModel();
    }

    public function index(): void {
        $search = $_GET['search'] ?? '';
        $customers = $search ? $this->customerModel->search($search) : $this->customerModel->findAll('', 'name ASC');
        $this->view('customers/index', [
            'pageTitle' => 'Customers',
            'customers' => $customers,
            'search' => $search,
            'flash' => $this->getFlash(),
        ]);
    }

    public function create(): void {
        if ($this->isPost()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'phone' => $this->sanitize($_POST['phone'] ?? ''),
                'address' => $this->sanitize($_POST['address'] ?? ''),
            ];
            $id = $this->customerModel->create($data);
            if ($id) {
                $this->setFlash('success', 'Customer created!');
            } else {
                $this->setFlash('error', 'Failed to create customer.');
            }
            $this->redirect('customers');
        }

        $this->view('customers/form', [
            'pageTitle' => 'Add Customer',
            'customer' => null,
        ]);
    }

    public function edit($id): void {
        $customer = $this->customerModel->findById((int)$id);
        if (!$customer) {
            $this->setFlash('error', 'Customer not found.');
            $this->redirect('customers');
        }

        if ($this->isPost()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'phone' => $this->sanitize($_POST['phone'] ?? ''),
                'address' => $this->sanitize($_POST['address'] ?? ''),
            ];
            $this->customerModel->update((int)$id, $data);
            $this->setFlash('success', 'Customer updated!');
            $this->redirect('customers');
        }

        $this->view('customers/form', [
            'pageTitle' => 'Edit Customer',
            'customer' => $customer,
        ]);
    }

    public function delete($id): void {
        $this->requireAdmin();
        $this->customerModel->delete((int)$id);
        $this->setFlash('success', 'Customer deleted.');
        $this->redirect('customers');
    }
}
