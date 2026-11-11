<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\KategoriKeahlian;
use App\Models\Keahlian;
use App\Models\PesanKontak;
use App\Models\PengalamanKerja;
use App\Models\PengaturanSitus;
use App\Models\Profil;
use App\Models\Proyek;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    public function index(string $resource): View
    {
        $definition = $this->definition($resource);
        $model = $definition['model'];
        $records = $model->newQuery()->latest()->paginate(12)->withQueryString();

        return view('admin.cms.index', [
            'resource' => $resource,
            'definition' => $definition,
            'records' => $records,
        ]);
    }

    public function create(string $resource): View
    {
        $definition = $this->definition($resource);
        abort_unless($definition['create'], Response::HTTP_NOT_FOUND);

        return $this->formView($resource, $definition, $definition['model']->newInstance(), false);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_unless($definition['create'], Response::HTTP_NOT_FOUND);

        $data = $this->validatedData($request, $definition);
        $definition['model']->newInstance($data)->save();

        return redirect()
            ->route('admin.cms.index', $resource)
            ->with('status', $definition['singular'].' created.');
    }

    public function editSingleton(string $resource): View
    {
        $definition = $this->definition($resource);
        abort_unless($definition['singleton'], Response::HTTP_NOT_FOUND);
        $record = $definition['model']->newQuery()->first() ?? $definition['model']->newInstance();

        return $this->formView($resource, $definition, $record, true);
    }

    public function updateSingleton(Request $request, string $resource): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_unless($definition['singleton'], Response::HTTP_NOT_FOUND);
        $record = $definition['model']->newQuery()->first() ?? $definition['model']->newInstance();
        $record->fill($this->validatedData($request, $definition, $record));
        $record->save();

        return redirect()
            ->route('admin.cms.index', $resource)
            ->with('status', $definition['singular'].' updated.');
    }

    public function edit(string $resource, int $record): View
    {
        $definition = $this->definition($resource);
        abort_if($definition['singleton'], Response::HTTP_NOT_FOUND);
        $model = $definition['model']->newQuery()->findOrFail($record);

        return $this->formView($resource, $definition, $model, false);
    }

    public function update(Request $request, string $resource, int $record): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_if($definition['singleton'], Response::HTTP_NOT_FOUND);
        $model = $definition['model']->newQuery()->findOrFail($record);
        $model->fill($this->validatedData($request, $definition, $model));
        $model->save();

        return redirect()
            ->route('admin.cms.index', $resource)
            ->with('status', $definition['singular'].' updated.');
    }

    public function destroy(string $resource, int $record): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_unless($definition['delete'], Response::HTTP_NOT_FOUND);
        $definition['model']->newQuery()->findOrFail($record)->delete();

        return redirect()
            ->route('admin.cms.index', $resource)
            ->with('status', $definition['singular'].' deleted.');
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function formView(string $resource, array $definition, Model $record, bool $singleton): View
    {
        $options = [];

        if (in_array($resource, ['skills'], true)) {
            $options['kategori_keahlian_id'] = KategoriKeahlian::query()->orderBy('urutan')->pluck('nama', 'id');
        }

        return view('admin.cms.form', [
            'resource' => $resource,
            'definition' => $definition,
            'record' => $record,
            'singleton' => $singleton,
            'options' => $options,
        ]);
    }

    /**
     * @param array<string, mixed> $definition
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, array $definition, ?Model $record = null): array
    {
        $rules = [];

        foreach ($definition['fields'] as $name => $field) {
            $fieldRules = $field['rules'] ?? ['nullable', 'string'];

            if (($field['unique'] ?? false) === true) {
                $fieldRules[] = Rule::unique($definition['table'], $name)->ignore($record?->getKey());
            }

            $rules[$name] = $fieldRules;
        }

        $validated = $request->validate($rules);

        foreach ($definition['fields'] as $name => $field) {
            if ($field['type'] === 'checkbox') {
                $validated[$name] = $request->boolean($name);
            } elseif ($field['type'] === 'list') {
                $lines = preg_split('/\r\n|\r|\n/', (string) ($validated[$name] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                $validated[$name] = array_values(array_filter(array_map('trim', $lines ?: [])));
            } elseif ($field['type'] === 'datetime' && ! empty($validated[$name])) {
                $validated[$name] = Carbon::parse($validated[$name])->toDateTimeString();
            }
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(string $resource): array
    {
        $definitions = [
            'profile' => [
                'label' => 'Profile',
                'singular' => 'Profile',
                'model' => new Profil,
                'table' => 'profil',
                'singleton' => true,
                'create' => false,
                'delete' => false,
                'columns' => ['nama_lengkap', 'jabatan', 'email'],
                'fields' => [
                    'nama_lengkap' => ['label' => 'Full name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'jabatan' => ['label' => 'Professional title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'ringkasan' => ['label' => 'Short bio', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:255']],
                    'biografi' => ['label' => 'About', 'type' => 'textarea'],
                    'url_foto' => ['label' => 'Photo URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'status_ketersediaan' => ['label' => 'Availability', 'type' => 'text'],
                    'email' => ['label' => 'Contact email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
                    'telepon' => ['label' => 'Phone', 'type' => 'text'],
                    'url_linkedin' => ['label' => 'LinkedIn URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_github' => ['label' => 'GitHub URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_instagram' => ['label' => 'Instagram URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_whatsapp' => ['label' => 'WhatsApp URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'lokasi' => ['label' => 'Location', 'type' => 'text'],
                ],
            ],
            'projects' => [
                'label' => 'Projects',
                'singular' => 'Project',
                'model' => new Proyek,
                'table' => 'proyek',
                'singleton' => false,
                'create' => true,
                'delete' => true,
                'columns' => ['judul', 'kategori', 'status', 'unggulan'],
                'fields' => [
                    'judul' => ['label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['required', 'alpha_dash:ascii', 'max:255'], 'unique' => true],
                    'kategori' => ['label' => 'Category', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'ringkasan' => ['label' => 'Summary', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:255']],
                    'pernyataan_masalah' => ['label' => 'Problem', 'type' => 'textarea'],
                    'ringkasan_solusi' => ['label' => 'Solution', 'type' => 'textarea'],
                    'tumpukan_teknologi' => ['label' => 'Tech stack', 'type' => 'list'],
                    'fitur_utama' => ['label' => 'Key features', 'type' => 'list'],
                    'ringkasan_arsitektur' => ['label' => 'Architecture', 'type' => 'textarea'],
                    'url_gambar_pratinjau' => ['label' => 'Preview image URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'unggulan' => ['label' => 'Featured', 'type' => 'checkbox'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'published' => 'Published'], 'rules' => ['required', 'in:draft,published']],
                    'url_demo' => ['label' => 'Live demo URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_github' => ['label' => 'GitHub URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'hasil_proyek' => ['label' => 'Outcome', 'type' => 'textarea'],
                    'diterbitkan_pada' => ['label' => 'Published at', 'type' => 'datetime'],
                ],
            ],
            'skill-categories' => [
                'label' => 'Skill categories',
                'singular' => 'Skill category',
                'model' => new KategoriKeahlian,
                'table' => 'kategori_keahlian',
                'singleton' => false,
                'create' => true,
                'delete' => true,
                'columns' => ['nama', 'slug', 'urutan'],
                'fields' => [
                    'nama' => ['label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['required', 'alpha_dash:ascii', 'max:255'], 'unique' => true],
                    'urutan' => ['label' => 'Sort order', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
                ],
            ],
            'skills' => [
                'label' => 'Skills',
                'singular' => 'Skill',
                'model' => new Keahlian,
                'table' => 'keahlian',
                'singleton' => false,
                'create' => true,
                'delete' => true,
                'columns' => ['nama', 'kategori_keahlian_id', 'tingkat', 'urutan'],
                'fields' => [
                    'kategori_keahlian_id' => ['label' => 'Category', 'type' => 'select', 'rules' => ['required', 'exists:kategori_keahlian,id']],
                    'nama' => ['label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'deskripsi' => ['label' => 'Description', 'type' => 'textarea'],
                    'tingkat' => ['label' => 'Level', 'type' => 'text'],
                    'ikon' => ['label' => 'Icon key', 'type' => 'text'],
                    'urutan' => ['label' => 'Sort order', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
                ],
            ],
            'experience' => [
                'label' => 'Experience',
                'singular' => 'Experience',
                'model' => new PengalamanKerja,
                'table' => 'pengalaman_kerja',
                'singleton' => false,
                'create' => true,
                'delete' => true,
                'columns' => ['jabatan', 'organisasi', 'tanggal_mulai', 'tanggal_selesai'],
                'fields' => [
                    'organisasi' => ['label' => 'Organization', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'jabatan' => ['label' => 'Position', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'jenis_pekerjaan' => ['label' => 'Employment type', 'type' => 'text'],
                    'tanggal_mulai' => ['label' => 'Start date', 'type' => 'date', 'rules' => ['required', 'date']],
                    'tanggal_selesai' => ['label' => 'End date', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:tanggal_mulai']],
                    'deskripsi' => ['label' => 'Description', 'type' => 'textarea'],
                    'tanggung_jawab' => ['label' => 'Responsibilities', 'type' => 'list'],
                    'pencapaian' => ['label' => 'Achievements', 'type' => 'list'],
                    'teknologi' => ['label' => 'Technologies', 'type' => 'list'],
                    'urutan' => ['label' => 'Sort order', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
                ],
            ],
            'articles' => [
                'label' => 'Articles',
                'singular' => 'Article',
                'model' => new Artikel,
                'table' => 'artikel',
                'singleton' => false,
                'create' => true,
                'delete' => true,
                'columns' => ['judul', 'kategori', 'status', 'diterbitkan_pada'],
                'fields' => [
                    'judul' => ['label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['required', 'alpha_dash:ascii', 'max:255'], 'unique' => true],
                    'kategori' => ['label' => 'Category', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'ringkasan' => ['label' => 'Excerpt', 'type' => 'textarea'],
                    'isi' => ['label' => 'Content (HTML)', 'type' => 'textarea', 'rules' => ['required', 'string']],
                    'url_gambar_utama' => ['label' => 'Featured image URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'waktu_baca' => ['label' => 'Reading time (minutes)', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1']],
                    'unggulan' => ['label' => 'Featured', 'type' => 'checkbox'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'published' => 'Published'], 'rules' => ['required', 'in:draft,published']],
                    'judul_seo' => ['label' => 'SEO title', 'type' => 'text'],
                    'deskripsi_seo' => ['label' => 'SEO description', 'type' => 'textarea'],
                    'diterbitkan_pada' => ['label' => 'Published at', 'type' => 'datetime'],
                ],
            ],
            'messages' => [
                'label' => 'Contact messages',
                'singular' => 'Message',
                'model' => new PesanKontak,
                'table' => 'pesan_kontak',
                'singleton' => false,
                'create' => false,
                'delete' => true,
                'columns' => ['nama', 'email', 'subjek', 'status'],
                'fields' => [
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'], 'rules' => ['required', 'in:new,read,replied,archived']],
                ],
            ],
            'settings' => [
                'label' => 'Site settings',
                'singular' => 'Settings',
                'model' => new PengaturanSitus,
                'table' => 'pengaturan_situs',
                'singleton' => true,
                'create' => false,
                'delete' => false,
                'columns' => ['nama_situs', 'email_utama', 'mode_pemeliharaan'],
                'fields' => [
                    'nama_situs' => ['label' => 'Site name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slogan' => ['label' => 'Tagline', 'type' => 'text'],
                    'email_utama' => ['label' => 'Primary email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
                    'url_linkedin' => ['label' => 'LinkedIn URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_github' => ['label' => 'GitHub URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_instagram' => ['label' => 'Instagram URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_whatsapp' => ['label' => 'WhatsApp URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_logo' => ['label' => 'Logo URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'url_favicon' => ['label' => 'Favicon URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']],
                    'mode_pemeliharaan' => ['label' => 'Maintenance mode', 'type' => 'checkbox'],
                ],
            ],
        ];

        abort_unless(isset($definitions[$resource]), Response::HTTP_NOT_FOUND);

        return $definitions[$resource];
    }
}
