<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\KategoriKeahlian;
use App\Models\Keahlian;
use App\Models\PengalamanKerja;
use App\Models\PengaturanSitus;
use App\Models\Profil;
use App\Models\Proyek;
use App\Models\TagArtikel;
use Illuminate\Database\Seeder;

class DataPortofolioSeeder extends Seeder
{
    public function run(): void
    {
        Profil::query()->updateOrCreate(
            ['id' => 1],
            [
                'nama_lengkap' => 'Fadil',
                'jabatan' => 'System Analyst • Data Analyst • Software Developer',
                'ringkasan' => 'Turning complex problems into smart digital solutions.',
                'biografi' => 'I design systems, turn data into decisions, and build technology that creates measurable impact for businesses.',
                'url_foto' => null,
                'status_ketersediaan' => 'Available for projects',
                'email' => null,
                'telepon' => null,
                'url_linkedin' => null,
                'url_github' => null,
                'url_instagram' => null,
                'url_whatsapp' => null,
                'lokasi' => 'Indonesia',
            ]
        );

        PengaturanSitus::query()->updateOrCreate(
            ['id' => 1],
            [
                'nama_situs' => 'OBSIDIAN SIGNAL',
                'slogan' => 'Turning complex problems into smart digital solutions.',
                'email_utama' => null,
                'url_linkedin' => null,
                'url_github' => null,
                'url_instagram' => null,
                'url_whatsapp' => null,
                'mode_pemeliharaan' => false,
            ]
        );

        $kategoriAnalisisSistem = KategoriKeahlian::query()->updateOrCreate(
            ['slug' => 'system-analysis'],
            ['nama' => 'System Analysis & Design', 'urutan' => 1]
        );

        $kategoriAnalisisData = KategoriKeahlian::query()->updateOrCreate(
            ['slug' => 'data-analysis'],
            ['nama' => 'Data Analysis', 'urutan' => 2]
        );

        $kategoriRekayasaPerangkatLunak = KategoriKeahlian::query()->updateOrCreate(
            ['slug' => 'software-engineering'],
            ['nama' => 'Software Engineering', 'urutan' => 3]
        );

        $daftarKeahlian = [
            [$kategoriAnalisisSistem->id, 'Requirement Analysis', 'Gathering and validating business needs into actionable system requirements.', 'Advanced', 'layers'],
            [$kategoriAnalisisSistem->id, 'Business Process Modeling', 'Mapping processes and pain points for decision-ready system design.', 'Advanced', 'workflow'],
            [$kategoriAnalisisSistem->id, 'UML & System Architecture', 'Designing diagrams, flows, and technical blueprints for sustainable implementation.', 'Advanced', 'diagram'],
            [$kategoriAnalisisData->id, 'Data Cleaning', 'Preprocessing raw data into consistent and reliable analysis-ready assets.', 'Advanced', 'database'],
            [$kategoriAnalisisData->id, 'Dashboard Development', 'Building dashboards that communicate trends and operational signals clearly.', 'Advanced', 'chart'],
            [$kategoriRekayasaPerangkatLunak->id, 'Laravel', 'Building maintainable backend systems with clear domain boundaries and strong conventions.', 'Advanced', 'code'],
            [$kategoriRekayasaPerangkatLunak->id, 'JavaScript', 'Front-end interaction, UI logic, and product-level state handling.', 'Advanced', 'javascript'],
            [$kategoriRekayasaPerangkatLunak->id, 'REST API', 'Designing clear endpoints and integrations for connected systems.', 'Advanced', 'server'],
        ];

        foreach ($daftarKeahlian as $indeks => [$kategoriId, $nama, $deskripsi, $tingkat, $ikon]) {
            Keahlian::query()->updateOrCreate(
                ['nama' => $nama],
                [
                    'kategori_keahlian_id' => $kategoriId,
                    'deskripsi' => $deskripsi,
                    'tingkat' => $tingkat,
                    'ikon' => $ikon,
                    'urutan' => $indeks + 1,
                ]
            );
        }

        Proyek::query()->updateOrCreate(
            ['slug' => 'integrated-management-information-system'],
            [
                'judul' => 'Integrated Management Information System',
                'kategori' => 'System Information',
                'ringkasan' => 'A centralized business operations platform connecting strategic, operational, and reporting workflows.',
                'pernyataan_masalah' => 'Teams were working across disconnected tools with inconsistent reporting and delayed operational decisions.',
                'ringkasan_solusi' => 'The system consolidated operations into one coherent workflow with structured data capture, dashboards, and role-based access.',
                'tumpukan_teknologi' => ['Laravel', 'MySQL', 'Tailwind', 'Alpine.js'],
                'fitur_utama' => ['Operational dashboard', 'Role-based access', 'Reporting', 'Workflow automation'],
                'ringkasan_arsitektur' => 'The application was structured around a modular Laravel service layer and shared data model for reporting and workflow orchestration.',
                'url_gambar_pratinjau' => null,
                'unggulan' => true,
                'status' => 'published',
                'url_demo' => '#',
                'url_github' => '#',
                'hasil_proyek' => 'Improved visibility and reduced manual coordination between teams.',
                'diterbitkan_pada' => now(),
            ]
        );

        Proyek::query()->updateOrCreate(
            ['slug' => 'healthcare-monitoring-dashboard'],
            [
                'judul' => 'Healthcare Monitoring Dashboard',
                'kategori' => 'Healthcare Technology',
                'ringkasan' => 'A monitoring and reporting dashboard for healthcare operations and screening activities.',
                'pernyataan_masalah' => 'Operational monitoring was fragmented and difficult to act on in time.',
                'ringkasan_solusi' => 'The dashboard centralized health metrics and workflows into a clear operational view.',
                'tumpukan_teknologi' => ['Laravel', 'Chart.js', 'PostgreSQL'],
                'fitur_utama' => ['Executive dashboard', 'Monitoring panels', 'Trends and alerts'],
                'ringkasan_arsitektur' => 'The system used a reporting-first architecture with shared analytic queries and role-based views.',
                'url_gambar_pratinjau' => null,
                'unggulan' => true,
                'status' => 'published',
                'diterbitkan_pada' => now()->subDay(),
            ]
        );

        PengalamanKerja::query()->updateOrCreate(
            ['organisasi' => 'Independent Consultant'],
            [
                'jabatan' => 'System Analyst & Digital Solution Builder',
                'jenis_pekerjaan' => 'Freelance',
                'tanggal_mulai' => '2023-01-01',
                'tanggal_selesai' => null,
                'deskripsi' => 'Designing digital systems and data-driven solutions for business transformation.',
                'tanggung_jawab' => ['Requirement analysis', 'Process mapping', 'System design', 'Implementation support'],
                'pencapaian' => ['Built reusable architecture patterns', 'Improved decision support'],
                'teknologi' => ['Laravel', 'MySQL', 'Tailwind', 'Python'],
                'urutan' => 1,
            ]
        );

        $tagAnalisisSistem = TagArtikel::query()->updateOrCreate(['slug' => 'system-analysis'], ['nama' => 'System Analysis']);
        $tagAnalitikData = TagArtikel::query()->updateOrCreate(['slug' => 'data-analytics'], ['nama' => 'Data Analytics']);
        $tagPemrogramanAi = TagArtikel::query()->updateOrCreate(['slug' => 'ai-vibe-coding'], ['nama' => 'AI & Vibe Coding']);

        $artikel = Artikel::query()->updateOrCreate(
            ['slug' => 'turning-business-needs-into-system-design'],
            [
                'judul' => 'Turning Business Needs Into System Design',
                'kategori' => 'System Analysis',
                'ringkasan' => 'A practical way to translate business complexity into architecture decisions that are usable and maintainable.',
                'isi' => '<p>Every solution starts with understanding the real needs behind the workflow.</p><p>From requirements to logic and data flows, the design process must remain grounded in concrete operational realities.</p>',
                'url_gambar_utama' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                'waktu_baca' => 6,
                'unggulan' => true,
                'status' => 'published',
                'judul_seo' => 'Turning Business Needs Into System Design',
                'deskripsi_seo' => 'A practical guide to translating business needs into system design decisions.',
                'diterbitkan_pada' => now(),
            ]
        );

        $artikel->tagArtikel()->syncWithoutDetaching([
            $tagAnalisisSistem->id,
            $tagAnalitikData->id,
            $tagPemrogramanAi->id,
        ]);
    }
}
