--
-- PostgreSQL database dump
--

\restrict e5qrXo13sH3fphBbihdebbgqnmOHJL6nCHKBdfjy3ZUnCwuz6pC7xJvtsc6I3TF

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-12-14 20:27:50

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 263 (class 1255 OID 66605)
-- Name: sp_get_konten(integer, character varying, character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_get_konten(p_id_user integer, p_role character varying, p_status character varying DEFAULT NULL::character varying, p_kategori character varying DEFAULT NULL::character varying, p_limit integer DEFAULT 100) RETURNS TABLE(id_konten integer, kategori_konten character varying, judul character varying, slug character varying, isi text, gambar character varying, urutan integer, status character varying, tanggal_posting timestamp without time zone, author_nama character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF p_role = 'admin' THEN
        -- Admin bisa lihat semua konten
        RETURN QUERY
        SELECT 
            k.id_konten,
            k.kategori_konten,
            k.judul,
            k.slug,
            k.isi,
            k.gambar,
            k.urutan,
            k.status,
            k.tanggal_posting,
            u.nama::VARCHAR
        FROM konten k
        LEFT JOIN users u ON k.id_user = u.id_user
        WHERE (p_status IS NULL OR k.status = p_status)
          AND (p_kategori IS NULL OR k.kategori_konten = p_kategori)
        ORDER BY k.urutan ASC, k.tanggal_posting DESC
        LIMIT p_limit;
    
    ELSIF p_role = 'operator' THEN
        -- Operator hanya lihat konten miliknya
        RETURN QUERY
        SELECT 
            k.id_konten,
            k.kategori_konten,
            k.judul,
            k.slug,
            k.isi,
            k.gambar,
            k.urutan,
            k.status,
            k.tanggal_posting,
            u.nama::VARCHAR
        FROM konten k
        LEFT JOIN users u ON k.id_user = u.id_user
        WHERE k.id_user = p_id_user
          AND (p_status IS NULL OR k.status = p_status)
          AND (p_kategori IS NULL OR k.kategori_konten = p_kategori)
        ORDER BY k.urutan ASC, k.tanggal_posting DESC
        LIMIT p_limit;
    END IF;
END;
$$;


ALTER FUNCTION public.sp_get_konten(p_id_user integer, p_role character varying, p_status character varying, p_kategori character varying, p_limit integer) OWNER TO postgres;

--
-- TOC entry 262 (class 1255 OID 62009)
-- Name: sp_get_riwayat_admin(character varying, character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_get_riwayat_admin(p_status character varying DEFAULT NULL::character varying, p_tabel character varying DEFAULT NULL::character varying, p_bulan character varying DEFAULT NULL::character varying, p_limit integer DEFAULT 200) RETURNS TABLE(id_riwayat integer, tabel_sumber character varying, id_data integer, id_operator integer, id_admin integer, status_lama character varying, status_baru character varying, catatan text, created_at timestamp without time zone, operator_nama character varying, admin_nama character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        r.id_riwayat,
        r.tabel_sumber,
        r.id_data,
        r.id_operator,
        r.id_admin,
        r.status_lama,
        r.status_baru,
        r.catatan,
        r.created_at,
        u_op.nama::VARCHAR as operator_nama,
        u_adm.nama::VARCHAR as admin_nama
    FROM riwayat_pengajuan r
    LEFT JOIN users u_op ON r.id_operator = u_op.id_user
    LEFT JOIN users u_adm ON r.id_admin = u_adm.id_user
    WHERE (p_status IS NULL OR r.status_baru = p_status)
      AND (p_tabel IS NULL OR r.tabel_sumber = p_tabel)
      AND (p_bulan IS NULL OR TO_CHAR(r.created_at, 'YYYY-MM') = p_bulan)
    ORDER BY r.created_at DESC
    LIMIT p_limit;
END;
$$;


ALTER FUNCTION public.sp_get_riwayat_admin(p_status character varying, p_tabel character varying, p_bulan character varying, p_limit integer) OWNER TO postgres;

--
-- TOC entry 269 (class 1255 OID 66622)
-- Name: sp_get_riwayat_admin(character varying, character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_get_riwayat_admin(p_status character varying DEFAULT NULL::character varying, p_tabel character varying DEFAULT NULL::character varying, p_bulan character varying DEFAULT NULL::character varying, p_limit integer DEFAULT 20, p_offset integer DEFAULT 0) RETURNS TABLE(id_riwayat integer, created_at timestamp without time zone, tabel_sumber character varying, id_data text, status_lama character varying, status_baru character varying, catatan text, operator_nama character varying, admin_nama character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    
    -- UNION semua sumber data
    WITH all_riwayat AS (
        -- 1. Data dari riwayat_pengajuan
        SELECT 
            r.id_riwayat,
            r.created_at,
            r.tabel_sumber,
            r.id_data::text as id_data,
            r.status_lama,
            r.status_baru,
            r.catatan,
            uo.nama as operator_nama,
            ua.nama as admin_nama
        FROM riwayat_pengajuan r
        LEFT JOIN users uo ON r.id_operator = uo.id_user
        LEFT JOIN users ua ON r.id_admin = ua.id_user
        
        UNION ALL

		SELECT 
            NULL as id_riwayat,
            k.updated_at as created_at,
            'kontak'::VARCHAR(50) as tabel_sumber,
            k.id_kontak::text as id_data,
            NULL as status_lama,
            k.status as status_baru,
            CONCAT('Kontak - ', k.email) as catatan,
            u.nama as operator_nama,
            NULL as admin_nama
        FROM kontak k
        LEFT JOIN users u ON k.id_user = u.id_user
        WHERE k.status IN ('pending', 'rejected', 'active')
        
        UNION ALL

		SELECT 
            NULL as id_riwayat,
            t.updated_at as created_at,
            'tentang_kami'::VARCHAR(50) as tabel_sumber,
            t.id_profil::text as id_data,
            NULL as status_lama,
            t.status as status_baru,
            'Profil Lab' as catatan,
            u.nama as operator_nama,
            NULL as admin_nama
        FROM tentang_kami t
        LEFT JOIN users u ON t.id_user = u.id_user
        WHERE t.status IN ('pending', 'rejected', 'active')
        
        UNION ALL

		SELECT 
            NULL as id_riwayat,
            v.created_at,
            'visi'::VARCHAR(50) as tabel_sumber,
            v.id_visi::text as id_data,
            NULL as status_lama,
            v.status as status_baru,
            CONCAT('Visi: ', LEFT(v.isi_visi, 40), '...') as catatan,
            u.nama as operator_nama,
            NULL as admin_nama
        FROM visi v
        LEFT JOIN users u ON v.id_user = u.id_user
        WHERE v.status IN ('pending', 'rejected', 'active')
        
        UNION ALL

		  SELECT 
            NULL as id_riwayat,
            m.created_at,
            'misi'::VARCHAR(50) as tabel_sumber,
            m.id_misi::text as id_data,
            NULL as status_lama,
            m.status as status_baru,
            CONCAT('Misi #', m.urutan, ': ', LEFT(m.isi_misi, 40), '...') as catatan,
            u.nama as operator_nama,
            NULL as admin_nama
        FROM misi m
        LEFT JOIN users u ON m.id_user = u.id_user
        WHERE m.status IN ('pending', 'rejected', 'active')
        
        UNION ALL

		SELECT 
            NULL as id_riwayat,
            s.created_at,
            'sejarah'::VARCHAR(50) as tabel_sumber,
            s.id_sejarah::text as id_data,
            NULL as status_lama,
            s.status as status_baru,
            CONCAT('Roadmap ', s.tahun, ': ', s.judul) as catatan,
            u.nama as operator_nama,
            NULL as admin_nama
        FROM sejarah s
        LEFT JOIN users u ON s.id_user = u.id_user
        WHERE s.status IN ('pending', 'rejected', 'active')
    )
    
    SELECT 
        ar.id_riwayat,
        ar.created_at,
        ar.tabel_sumber,
        ar.id_data,
        ar.status_lama,
        ar.status_baru,
        ar.catatan,
        ar.operator_nama,
        ar.admin_nama
    FROM all_riwayat ar
    WHERE (p_status IS NULL OR ar.status_baru = p_status)
      AND (p_tabel IS NULL OR ar.tabel_sumber = p_tabel)
      AND (p_bulan IS NULL OR TO_CHAR(ar.created_at, 'YYYY-MM') = p_bulan)
    ORDER BY ar.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END;
$$;


ALTER FUNCTION public.sp_get_riwayat_admin(p_status character varying, p_tabel character varying, p_bulan character varying, p_limit integer, p_offset integer) OWNER TO postgres;

--
-- TOC entry 264 (class 1255 OID 62010)
-- Name: sp_get_riwayat_operator(integer, character varying, character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_get_riwayat_operator(p_id_user integer, p_status character varying DEFAULT NULL::character varying, p_tabel character varying DEFAULT NULL::character varying, p_bulan character varying DEFAULT NULL::character varying, p_limit integer DEFAULT 200) RETURNS TABLE(id_riwayat integer, tabel_sumber character varying, id_data integer, id_operator integer, id_admin integer, status_lama character varying, status_baru character varying, catatan text, created_at timestamp without time zone, admin_nama character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        r.id_riwayat,
        r.tabel_sumber,
        r.id_data,
        r.id_operator,
        r.id_admin,
        r.status_lama,
        r.status_baru,
        r.catatan,
        r.created_at,
        u_adm.nama::VARCHAR as admin_nama
    FROM riwayat_pengajuan r
    LEFT JOIN users u_adm ON r.id_admin = u_adm.id_user
    WHERE r.id_operator = p_id_user
      AND (p_status IS NULL OR r.status_baru = p_status)
      AND (p_tabel IS NULL OR r.tabel_sumber = p_tabel)
      AND (p_bulan IS NULL OR TO_CHAR(r.created_at, 'YYYY-MM') = p_bulan)
    ORDER BY r.created_at DESC
    LIMIT p_limit;
END;
$$;


ALTER FUNCTION public.sp_get_riwayat_operator(p_id_user integer, p_status character varying, p_tabel character varying, p_bulan character varying, p_limit integer) OWNER TO postgres;

--
-- TOC entry 268 (class 1255 OID 66623)
-- Name: sp_get_riwayat_operator(integer, character varying, character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_get_riwayat_operator(p_id_user integer, p_status character varying DEFAULT NULL::character varying, p_tabel character varying DEFAULT NULL::character varying, p_bulan character varying DEFAULT NULL::character varying, p_limit integer DEFAULT 20, p_offset integer DEFAULT 0) RETURNS TABLE(created_at timestamp without time zone, tabel_sumber character varying, id_data text, status_lama character varying, status_baru character varying, catatan text, admin_nama character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    
    WITH all_riwayat AS (
        -- 1. Data dari riwayat_pengajuan
        SELECT 
            r.created_at,
            r.tabel_sumber,
            r.id_data::text as id_data,
            r.status_lama,
            r.status_baru,
            r.catatan,
            ua.nama as admin_nama
        FROM riwayat_pengajuan r
        LEFT JOIN users ua ON r.id_admin = ua.id_user
        WHERE r.id_operator = p_id_user
        
        UNION ALL
        
        -- 2. Data dari kontak
        SELECT 
            k.updated_at as created_at,
            'kontak'::VARCHAR(50) as tabel_sumber,
            k.id_kontak::text as id_data,
            NULL as status_lama,
            k.status as status_baru,
            CONCAT('Kontak - ', k.email) as catatan,
            NULL as admin_nama
        FROM kontak k
        WHERE k.id_user = p_id_user 
          AND k.status IN ('pending', 'rejected', 'active')
        
        UNION ALL
        
        -- 3. Data dari tentang_kami
        SELECT 
            t.updated_at as created_at,
            'tentang_kami'::VARCHAR(50) as tabel_sumber,
            t.id_profil::text as id_data,
            NULL as status_lama,
            t.status as status_baru,
            'Profil Lab' as catatan,
            NULL as admin_nama
        FROM tentang_kami t
        WHERE t.id_user = p_id_user 
          AND t.status IN ('pending', 'rejected', 'active')
        
        UNION ALL
        
        -- 4. Data dari visi
        SELECT 
            v.created_at,
            'visi'::VARCHAR(50) as tabel_sumber,
            v.id_visi::text as id_data,
            NULL as status_lama,
            v.status as status_baru,
            CONCAT('Visi: ', LEFT(v.isi_visi, 40), '...') as catatan,
            NULL as admin_nama
        FROM visi v
        WHERE v.id_user = p_id_user 
          AND v.status IN ('pending', 'rejected', 'active')
        
        UNION ALL
        
        -- 5. Data dari misi
        SELECT 
            m.created_at,
            'misi'::VARCHAR(50) as tabel_sumber,
            m.id_misi::text as id_data,
            NULL as status_lama,
            m.status as status_baru,
            CONCAT('Misi #', m.urutan, ': ', LEFT(m.isi_misi, 40), '...') as catatan,
            NULL as admin_nama
        FROM misi m
        WHERE m.id_user = p_id_user 
          AND m.status IN ('pending', 'rejected', 'active')
        
        UNION ALL
        
        -- 6. Data dari sejarah
        SELECT 
            s.created_at,
            'sejarah'::VARCHAR(50) as tabel_sumber,
            s.id_sejarah::text as id_data,
            NULL as status_lama,
            s.status as status_baru,
            CONCAT('Roadmap ', s.tahun, ': ', s.judul) as catatan,
            NULL as admin_nama
        FROM sejarah s
        WHERE s.id_user = p_id_user 
          AND s.status IN ('pending', 'rejected', 'active')
    )
    
    SELECT 
        ar.created_at,
        ar.tabel_sumber,
        ar.id_data,
        ar.status_lama,
        ar.status_baru,
        ar.catatan,
        ar.admin_nama
    FROM all_riwayat ar
    WHERE (p_status IS NULL OR ar.status_baru = p_status)
      AND (p_tabel IS NULL OR ar.tabel_sumber = p_tabel)
      AND (p_bulan IS NULL OR TO_CHAR(ar.created_at, 'YYYY-MM') = p_bulan)
    ORDER BY ar.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END;
$$;


ALTER FUNCTION public.sp_get_riwayat_operator(p_id_user integer, p_status character varying, p_tabel character varying, p_bulan character varying, p_limit integer, p_offset integer) OWNER TO postgres;

--
-- TOC entry 267 (class 1255 OID 66624)
-- Name: sp_get_riwayat_stats_admin(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_get_riwayat_stats_admin() RETURNS TABLE(pending bigint, approved bigint, rejected bigint, deleted bigint, total bigint)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    WITH all_data AS (
        SELECT status_baru FROM riwayat_pengajuan
        UNION ALL
        SELECT status FROM kontak WHERE status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM tentang_kami WHERE status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM visi WHERE status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM misi WHERE status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM sejarah WHERE status IN ('pending', 'rejected', 'active')
    )
    SELECT 
        COUNT(*) FILTER (WHERE status_baru = 'pending') as pending,
        COUNT(*) FILTER (WHERE status_baru = 'active') as approved,
        COUNT(*) FILTER (WHERE status_baru = 'rejected') as rejected,
        COUNT(*) FILTER (WHERE status_baru = 'deleted') as deleted,
        COUNT(*) as total
    FROM all_data;
END;
$$;


ALTER FUNCTION public.sp_get_riwayat_stats_admin() OWNER TO postgres;

--
-- TOC entry 270 (class 1255 OID 66625)
-- Name: sp_get_riwayat_stats_operator(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_get_riwayat_stats_operator(p_id_user integer) RETURNS TABLE(pending bigint, approved bigint, rejected bigint, deleted bigint, total bigint)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    WITH all_data AS (
        SELECT status_baru FROM riwayat_pengajuan WHERE id_operator = p_id_user
        UNION ALL
        SELECT status FROM kontak WHERE id_user = p_id_user AND status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM tentang_kami WHERE id_user = p_id_user AND status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM visi WHERE id_user = p_id_user AND status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM misi WHERE id_user = p_id_user AND status IN ('pending', 'rejected', 'active')
        UNION ALL
        SELECT status FROM sejarah WHERE id_user = p_id_user AND status IN ('pending', 'rejected', 'active')
    )
    SELECT 
        COUNT(*) FILTER (WHERE status_baru = 'pending') as pending,
        COUNT(*) FILTER (WHERE status_baru = 'active') as approved,
        COUNT(*) FILTER (WHERE status_baru = 'rejected') as rejected,
        COUNT(*) FILTER (WHERE status_baru = 'deleted') as deleted,
        COUNT(*) as total
    FROM all_data;
END;
$$;


ALTER FUNCTION public.sp_get_riwayat_stats_operator(p_id_user integer) OWNER TO postgres;

--
-- TOC entry 265 (class 1255 OID 66606)
-- Name: sp_insert_konten(character varying, character varying, character varying, text, character varying, integer, character varying, integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_insert_konten(p_kategori character varying, p_judul character varying, p_slug character varying, p_isi text, p_gambar character varying, p_urutan integer, p_status character varying, p_id_user integer, p_role character varying, OUT p_id_konten integer, OUT p_message character varying) RETURNS record
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Validasi input
    IF p_judul IS NULL OR p_judul = '' THEN
        p_message := 'Error: Judul tidak boleh kosong';
        p_id_konten := 0;
        RETURN;
    ELSIF p_isi IS NULL OR p_isi = '' THEN
        p_message := 'Error: Isi konten tidak boleh kosong';
        p_id_konten := 0;
        RETURN;
    END IF;

    -- Set default urutan jika NULL
    IF p_urutan IS NULL OR p_urutan = 0 THEN
        p_urutan := 1;
    END IF;

    -- Set status otomatis berdasarkan role
    IF p_role = 'admin' THEN
        p_status := 'active';
    ELSE
        p_status := 'pending';
    END IF;

    -- INSERT
    INSERT INTO konten (
        kategori_konten, judul, slug, isi, gambar, urutan,
        status, id_user, tanggal_posting
    ) VALUES (
        p_kategori, p_judul, p_slug, p_isi, p_gambar, p_urutan,
        p_status, p_id_user, NOW()
    )
    RETURNING id_konten INTO p_id_konten;

    p_message := 'Success: Konten berhasil disimpan';

EXCEPTION WHEN OTHERS THEN
    p_message := 'Error: Gagal menyimpan konten';
    p_id_konten := 0;
END;
$$;


ALTER FUNCTION public.sp_insert_konten(p_kategori character varying, p_judul character varying, p_slug character varying, p_isi text, p_gambar character varying, p_urutan integer, p_status character varying, p_id_user integer, p_role character varying, OUT p_id_konten integer, OUT p_message character varying) OWNER TO postgres;

--
-- TOC entry 266 (class 1255 OID 66607)
-- Name: sp_update_konten(integer, character varying, character varying, character varying, text, character varying, integer, character varying, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.sp_update_konten(p_id_konten integer, p_kategori character varying, p_judul character varying, p_slug character varying, p_isi text, p_gambar character varying, p_urutan integer, p_status character varying, p_id_user integer, OUT p_message character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_current_status VARCHAR(20);
BEGIN
    -- Cek kepemilikan data
    SELECT status INTO v_current_status 
    FROM konten 
    WHERE id_konten = p_id_konten AND id_user = p_id_user;

    IF v_current_status IS NULL THEN
        p_message := 'Error: Data tidak ditemukan atau Anda tidak punya akses';
        RETURN;
    END IF;

    -- Set default urutan jika NULL
    IF p_urutan IS NULL OR p_urutan = 0 THEN
        p_urutan := 1;
    END IF;

    -- UPDATE
    UPDATE konten 
    SET 
        kategori_konten = p_kategori,
        judul = p_judul,
        slug = p_slug,
        isi = p_isi,
        gambar = COALESCE(p_gambar, gambar),
        urutan = p_urutan,
        status = p_status
    WHERE id_konten = p_id_konten;

    p_message := 'Success: Konten berhasil diupdate';

EXCEPTION WHEN OTHERS THEN
    p_message := 'Error: Gagal update konten';
END;
$$;


ALTER FUNCTION public.sp_update_konten(p_id_konten integer, p_kategori character varying, p_judul character varying, p_slug character varying, p_isi text, p_gambar character varying, p_urutan integer, p_status character varying, p_id_user integer, OUT p_message character varying) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 233 (class 1259 OID 61838)
-- Name: anggota_lab; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.anggota_lab (
    id_anggota integer NOT NULL,
    nama character varying(150) NOT NULL,
    foto character varying(255),
    nip character varying(50),
    email character varying(100),
    kontak character varying(50),
    biodata_teks text,
    pendidikan text,
    bidang_keahlian text,
    tanggal_bergabung date,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    role_anggota character varying(20),
    CONSTRAINT anggota_lab_role_anggota_check CHECK (((role_anggota)::text = ANY ((ARRAY['dosen'::character varying, 'mahasiswa'::character varying])::text[]))),
    CONSTRAINT anggota_lab_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.anggota_lab OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 61837)
-- Name: anggota_lab_id_anggota_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.anggota_lab_id_anggota_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.anggota_lab_id_anggota_seq OWNER TO postgres;

--
-- TOC entry 3601 (class 0 OID 0)
-- Dependencies: 232
-- Name: anggota_lab_id_anggota_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.anggota_lab_id_anggota_seq OWNED BY public.anggota_lab.id_anggota;


--
-- TOC entry 244 (class 1259 OID 61943)
-- Name: fasilitas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fasilitas (
    id_fasilitas integer NOT NULL,
    judul character varying(150) NOT NULL,
    gambar character varying(255),
    deskripsi text,
    kategori_fasilitas character varying(100),
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fasilitas_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.fasilitas OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 61942)
-- Name: fasilitas_id_fasilitas_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fasilitas_id_fasilitas_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fasilitas_id_fasilitas_seq OWNER TO postgres;

--
-- TOC entry 3602 (class 0 OID 0)
-- Dependencies: 243
-- Name: fasilitas_id_fasilitas_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fasilitas_id_fasilitas_seq OWNED BY public.fasilitas.id_fasilitas;


--
-- TOC entry 227 (class 1259 OID 61789)
-- Name: footer; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.footer (
    id_footer integer NOT NULL,
    logo character varying(255),
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT footer_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.footer OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 61788)
-- Name: footer_id_footer_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.footer_id_footer_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.footer_id_footer_seq OWNER TO postgres;

--
-- TOC entry 3603 (class 0 OID 0)
-- Dependencies: 226
-- Name: footer_id_footer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.footer_id_footer_seq OWNED BY public.footer.id_footer;


--
-- TOC entry 246 (class 1259 OID 61960)
-- Name: galeri; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.galeri (
    id_galeri integer NOT NULL,
    gambar character varying(255) NOT NULL,
    judul character varying(255),
    deskripsi text,
    filter_kategori character varying(100),
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT galeri_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.galeri OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 61959)
-- Name: galeri_id_galeri_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.galeri_id_galeri_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.galeri_id_galeri_seq OWNER TO postgres;

--
-- TOC entry 3604 (class 0 OID 0)
-- Dependencies: 245
-- Name: galeri_id_galeri_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.galeri_id_galeri_seq OWNED BY public.galeri.id_galeri;


--
-- TOC entry 229 (class 1259 OID 61804)
-- Name: kontak; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.kontak (
    id_kontak integer NOT NULL,
    whatsapp character varying(255),
    email character varying(255),
    alamat text,
    linkedin character varying(255),
    jam_operasional character varying(255),
    instagram character varying(255),
    youtube character varying(255),
    facebook character varying(255),
    maps text,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT kontak_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.kontak OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 61803)
-- Name: kontak_id_kontak_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.kontak_id_kontak_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kontak_id_kontak_seq OWNER TO postgres;

--
-- TOC entry 3605 (class 0 OID 0)
-- Dependencies: 228
-- Name: kontak_id_kontak_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.kontak_id_kontak_seq OWNED BY public.kontak.id_kontak;


--
-- TOC entry 242 (class 1259 OID 61924)
-- Name: konten; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.konten (
    id_konten integer NOT NULL,
    kategori_konten character varying(100),
    judul character varying(255) NOT NULL,
    slug character varying(255),
    isi text,
    gambar character varying(255),
    tanggal_posting timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    CONSTRAINT konten_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.konten OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 61923)
-- Name: konten_id_konten_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.konten_id_konten_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.konten_id_konten_seq OWNER TO postgres;

--
-- TOC entry 3606 (class 0 OID 0)
-- Dependencies: 241
-- Name: konten_id_konten_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.konten_id_konten_seq OWNED BY public.konten.id_konten;


--
-- TOC entry 221 (class 1259 OID 61740)
-- Name: misi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.misi (
    id_misi integer NOT NULL,
    isi_misi text NOT NULL,
    urutan integer NOT NULL,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT misi_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.misi OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 61739)
-- Name: misi_id_misi_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.misi_id_misi_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.misi_id_misi_seq OWNER TO postgres;

--
-- TOC entry 3607 (class 0 OID 0)
-- Dependencies: 220
-- Name: misi_id_misi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.misi_id_misi_seq OWNED BY public.misi.id_misi;


--
-- TOC entry 225 (class 1259 OID 61774)
-- Name: navbar; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.navbar (
    id_nav integer NOT NULL,
    logo character varying(255),
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT navbar_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.navbar OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 61773)
-- Name: navbar_id_nav_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.navbar_id_nav_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.navbar_id_nav_seq OWNER TO postgres;

--
-- TOC entry 3608 (class 0 OID 0)
-- Dependencies: 224
-- Name: navbar_id_nav_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.navbar_id_nav_seq OWNED BY public.navbar.id_nav;


--
-- TOC entry 239 (class 1259 OID 61891)
-- Name: publikasi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.publikasi (
    id_publikasi integer NOT NULL,
    judul character varying(255) NOT NULL,
    cover character varying(255),
    abstrak text,
    tahun character varying(10),
    jurnal character varying(255),
    file_path character varying(255),
    link_shinta character varying(255),
    tanggal_publikasi date,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT publikasi_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.publikasi OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 61907)
-- Name: publikasi_anggota; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.publikasi_anggota (
    id_publikasi integer NOT NULL,
    id_anggota integer NOT NULL,
    urutan_penulis integer DEFAULT 1
);


ALTER TABLE public.publikasi_anggota OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 61890)
-- Name: publikasi_id_publikasi_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.publikasi_id_publikasi_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.publikasi_id_publikasi_seq OWNER TO postgres;

--
-- TOC entry 3609 (class 0 OID 0)
-- Dependencies: 238
-- Name: publikasi_id_publikasi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.publikasi_id_publikasi_seq OWNED BY public.publikasi.id_publikasi;


--
-- TOC entry 248 (class 1259 OID 61977)
-- Name: riwayat_pengajuan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.riwayat_pengajuan (
    id_riwayat integer NOT NULL,
    tabel_sumber character varying(100) NOT NULL,
    id_data integer NOT NULL,
    id_operator integer,
    id_admin integer,
    status_lama character varying(20),
    status_baru character varying(20),
    catatan text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.riwayat_pengajuan OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 61976)
-- Name: riwayat_pengajuan_id_riwayat_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.riwayat_pengajuan_id_riwayat_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.riwayat_pengajuan_id_riwayat_seq OWNER TO postgres;

--
-- TOC entry 3610 (class 0 OID 0)
-- Dependencies: 247
-- Name: riwayat_pengajuan_id_riwayat_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.riwayat_pengajuan_id_riwayat_seq OWNED BY public.riwayat_pengajuan.id_riwayat;


--
-- TOC entry 223 (class 1259 OID 61757)
-- Name: sejarah; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sejarah (
    id_sejarah integer NOT NULL,
    tahun character varying(10) NOT NULL,
    judul character varying(255) NOT NULL,
    deskripsi text,
    urutan integer,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT sejarah_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.sejarah OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 61756)
-- Name: sejarah_id_sejarah_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sejarah_id_sejarah_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sejarah_id_sejarah_seq OWNER TO postgres;

--
-- TOC entry 3611 (class 0 OID 0)
-- Dependencies: 222
-- Name: sejarah_id_sejarah_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sejarah_id_sejarah_seq OWNED BY public.sejarah.id_sejarah;


--
-- TOC entry 231 (class 1259 OID 61821)
-- Name: slider; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.slider (
    id_slider integer NOT NULL,
    gambar character varying(255),
    judul character varying(255),
    deskripsi text,
    urutan integer,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    tanggal_dibuat timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT slider_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.slider OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 61820)
-- Name: slider_id_slider_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.slider_id_slider_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.slider_id_slider_seq OWNER TO postgres;

--
-- TOC entry 3612 (class 0 OID 0)
-- Dependencies: 230
-- Name: slider_id_slider_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.slider_id_slider_seq OWNED BY public.slider.id_slider;


--
-- TOC entry 235 (class 1259 OID 61857)
-- Name: social_media_anggota; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.social_media_anggota (
    id_social integer NOT NULL,
    id_anggota integer NOT NULL,
    platform character varying(50),
    url character varying(255) NOT NULL,
    CONSTRAINT social_media_anggota_platform_check CHECK (((platform)::text = ANY ((ARRAY['email'::character varying, 'linkedin'::character varying, 'scholar'::character varying, 'instagram'::character varying, 'facebook'::character varying])::text[])))
);


ALTER TABLE public.social_media_anggota OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 61856)
-- Name: social_media_anggota_id_social_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.social_media_anggota_id_social_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.social_media_anggota_id_social_seq OWNER TO postgres;

--
-- TOC entry 3613 (class 0 OID 0)
-- Dependencies: 234
-- Name: social_media_anggota_id_social_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.social_media_anggota_id_social_seq OWNED BY public.social_media_anggota.id_social;


--
-- TOC entry 237 (class 1259 OID 61870)
-- Name: struktur_lab; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.struktur_lab (
    id_struktur integer NOT NULL,
    id_anggota integer NOT NULL,
    jabatan character varying(150) NOT NULL,
    urutan integer DEFAULT 0,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT struktur_lab_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.struktur_lab OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 61869)
-- Name: struktur_lab_id_struktur_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.struktur_lab_id_struktur_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.struktur_lab_id_struktur_seq OWNER TO postgres;

--
-- TOC entry 3614 (class 0 OID 0)
-- Dependencies: 236
-- Name: struktur_lab_id_struktur_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.struktur_lab_id_struktur_seq OWNED BY public.struktur_lab.id_struktur;


--
-- TOC entry 217 (class 1259 OID 61704)
-- Name: tentang_kami; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tentang_kami (
    id_profil integer NOT NULL,
    profil_lab text,
    logo_lab character varying(255),
    penjelasan_logo text,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT tentang_kami_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.tentang_kami OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 61703)
-- Name: tentang_kami_id_profil_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tentang_kami_id_profil_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tentang_kami_id_profil_seq OWNER TO postgres;

--
-- TOC entry 3615 (class 0 OID 0)
-- Dependencies: 216
-- Name: tentang_kami_id_profil_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tentang_kami_id_profil_seq OWNED BY public.tentang_kami.id_profil;


--
-- TOC entry 215 (class 1259 OID 61688)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id_user integer NOT NULL,
    username character varying(50) NOT NULL,
    password text NOT NULL,
    nama character varying(100),
    email character varying(100),
    role character varying(20) DEFAULT 'operator'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'operator'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 61687)
-- Name: users_id_user_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_user_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_user_seq OWNER TO postgres;

--
-- TOC entry 3616 (class 0 OID 0)
-- Dependencies: 214
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- TOC entry 249 (class 1259 OID 66612)
-- Name: v_riwayat_pengajuan; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_riwayat_pengajuan AS
 SELECT rp.id_riwayat,
    rp.created_at,
    rp.tabel_sumber,
    rp.id_data,
    rp.status_lama,
    rp.status_baru,
    rp.catatan,
    u_operator.nama AS operator_nama,
    u_operator.email AS operator_email,
    u_admin.nama AS admin_nama,
    u_admin.email AS admin_email
   FROM ((public.riwayat_pengajuan rp
     LEFT JOIN public.users u_operator ON ((rp.id_operator = u_operator.id_user)))
     LEFT JOIN public.users u_admin ON ((rp.id_admin = u_admin.id_user)));


ALTER TABLE public.v_riwayat_pengajuan OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 66617)
-- Name: v_riwayat_pengajuan_operator; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_riwayat_pengajuan_operator AS
 SELECT rp.id_riwayat,
    rp.created_at,
    rp.tabel_sumber,
    rp.id_data,
    rp.status_lama,
    rp.status_baru,
    rp.catatan,
    u_operator.nama AS operator_nama,
    u_operator.email AS operator_email,
    u_admin.nama AS admin_nama,
    u_admin.email AS admin_email
   FROM ((public.riwayat_pengajuan rp
     LEFT JOIN public.users u_operator ON ((rp.id_operator = u_operator.id_user)))
     LEFT JOIN public.users u_admin ON ((rp.id_admin = u_admin.id_user)))
  WHERE (rp.id_operator IS NOT NULL);


ALTER TABLE public.v_riwayat_pengajuan_operator OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 61722)
-- Name: visi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.visi (
    id_visi integer NOT NULL,
    isi_visi text NOT NULL,
    urutan integer DEFAULT 1,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT visi_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.visi OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 61721)
-- Name: visi_id_visi_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.visi_id_visi_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.visi_id_visi_seq OWNER TO postgres;

--
-- TOC entry 3617 (class 0 OID 0)
-- Dependencies: 218
-- Name: visi_id_visi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visi_id_visi_seq OWNED BY public.visi.id_visi;


--
-- TOC entry 3303 (class 2604 OID 61841)
-- Name: anggota_lab id_anggota; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab ALTER COLUMN id_anggota SET DEFAULT nextval('public.anggota_lab_id_anggota_seq'::regclass);


--
-- TOC entry 3318 (class 2604 OID 61946)
-- Name: fasilitas id_fasilitas; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fasilitas ALTER COLUMN id_fasilitas SET DEFAULT nextval('public.fasilitas_id_fasilitas_seq'::regclass);


--
-- TOC entry 3294 (class 2604 OID 61792)
-- Name: footer id_footer; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer ALTER COLUMN id_footer SET DEFAULT nextval('public.footer_id_footer_seq'::regclass);


--
-- TOC entry 3321 (class 2604 OID 61963)
-- Name: galeri id_galeri; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri ALTER COLUMN id_galeri SET DEFAULT nextval('public.galeri_id_galeri_seq'::regclass);


--
-- TOC entry 3297 (class 2604 OID 61807)
-- Name: kontak id_kontak; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kontak ALTER COLUMN id_kontak SET DEFAULT nextval('public.kontak_id_kontak_seq'::regclass);


--
-- TOC entry 3315 (class 2604 OID 61927)
-- Name: konten id_konten; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten ALTER COLUMN id_konten SET DEFAULT nextval('public.konten_id_konten_seq'::regclass);


--
-- TOC entry 3285 (class 2604 OID 61743)
-- Name: misi id_misi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.misi ALTER COLUMN id_misi SET DEFAULT nextval('public.misi_id_misi_seq'::regclass);


--
-- TOC entry 3291 (class 2604 OID 61777)
-- Name: navbar id_nav; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.navbar ALTER COLUMN id_nav SET DEFAULT nextval('public.navbar_id_nav_seq'::regclass);


--
-- TOC entry 3311 (class 2604 OID 61894)
-- Name: publikasi id_publikasi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi ALTER COLUMN id_publikasi SET DEFAULT nextval('public.publikasi_id_publikasi_seq'::regclass);


--
-- TOC entry 3324 (class 2604 OID 61980)
-- Name: riwayat_pengajuan id_riwayat; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan ALTER COLUMN id_riwayat SET DEFAULT nextval('public.riwayat_pengajuan_id_riwayat_seq'::regclass);


--
-- TOC entry 3288 (class 2604 OID 61760)
-- Name: sejarah id_sejarah; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sejarah ALTER COLUMN id_sejarah SET DEFAULT nextval('public.sejarah_id_sejarah_seq'::regclass);


--
-- TOC entry 3300 (class 2604 OID 61824)
-- Name: slider id_slider; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.slider ALTER COLUMN id_slider SET DEFAULT nextval('public.slider_id_slider_seq'::regclass);


--
-- TOC entry 3306 (class 2604 OID 61860)
-- Name: social_media_anggota id_social; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_media_anggota ALTER COLUMN id_social SET DEFAULT nextval('public.social_media_anggota_id_social_seq'::regclass);


--
-- TOC entry 3307 (class 2604 OID 61873)
-- Name: struktur_lab id_struktur; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab ALTER COLUMN id_struktur SET DEFAULT nextval('public.struktur_lab_id_struktur_seq'::regclass);


--
-- TOC entry 3277 (class 2604 OID 61707)
-- Name: tentang_kami id_profil; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tentang_kami ALTER COLUMN id_profil SET DEFAULT nextval('public.tentang_kami_id_profil_seq'::regclass);


--
-- TOC entry 3274 (class 2604 OID 61691)
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- TOC entry 3281 (class 2604 OID 61725)
-- Name: visi id_visi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visi ALTER COLUMN id_visi SET DEFAULT nextval('public.visi_id_visi_seq'::regclass);


--
-- TOC entry 3580 (class 0 OID 61838)
-- Dependencies: 233
-- Data for Name: anggota_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.anggota_lab (id_anggota, nama, foto, nip, email, kontak, biodata_teks, pendidikan, bidang_keahlian, tanggal_bergabung, id_user, status, created_at, role_anggota) FROM stdin;
3	mamad	anggota_1765344453.jpeg	2323223	kimintrulala@gmail.com	232323232	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	[]	[]	2025-12-10	2	active	2025-12-10 12:27:33.17285	dosen
2	zaki	anggota_1764756387.jpeg	123456789	kiminsetyawan@gmail.com	085334772234	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	[{"jenjang":"S3","institusi":"UI","tahun":"2025","jurusan":"Teknologi Informasi"},{"jenjang":"S2","institusi":"UI","tahun":"2022","jurusan":"Teknologi Informasi"}]	[{"nama":"UI UX"}]	2025-12-03	2	active	2025-12-03 17:06:27.601607	dosen
4	rafiqi	anggota_1765354303.jpeg	12345678	kimintrulala@gmail.com	83487278487978	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	[]	[]	2025-12-10	2	pending	2025-12-10 15:11:43.76623	mahasiswa
\.


--
-- TOC entry 3591 (class 0 OID 61943)
-- Dependencies: 244
-- Data for Name: fasilitas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fasilitas (id_fasilitas, judul, gambar, deskripsi, kategori_fasilitas, id_user, status, created_at) FROM stdin;
9	Lab Utama Data Technology	fasilitas_1765371939.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	Ruang Praktikum & Penelitian	2	active	2025-12-10 20:05:39.683167
5	Lab Utama Data Technology	fasilitas_1765276518.jpeg	Ruang praktikum utama dengan kapasitas 40 mahasiswa, dilengkapi AC, proyektor, dan whiteboard interaktif untuk pembelajaran yang efektif.	Ruang Praktikum & Penelitian	1	active	2025-12-09 17:35:18.856855
6	MySQL & PostgreSQL	fasilitas_1765276601.jpeg	Sistem manajemen database relasional untuk penyimpanan dan pengelolaan data terstruktur.	Perangkat Lunak	1	active	2025-12-09 17:36:41.017482
8	MySQL & PostgreSQL	fasilitas_1765371575.jpeg	MySQL & PostgreSQL	Perangkat Lunak	2	active	2025-12-10 19:59:35.995097
7	MySQL & PostgreSQL	fasilitas_1765371314.jpeg	MySQL & PostgreSQL	Perangkat Lunak	2	active	2025-12-10 19:55:14.658806
12	Laptop	fasilitas_1764811980.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	Perangkat Komputer	2	active	2025-12-10 20:07:12.716703
11	Laptop	fasilitas_1764811980.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	Perangkat Komputer	2	active	2025-12-10 20:07:03.367775
10	Lab Utama Data Technology	fasilitas_1765372006.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	Ruang Praktikum & Penelitian	2	active	2025-12-10 20:06:46.629297
4	Laptop	fasilitas_1764811980.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	Perangkat Komputer	2	active	2025-12-04 08:33:00.079598
\.


--
-- TOC entry 3574 (class 0 OID 61789)
-- Dependencies: 227
-- Data for Name: footer; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.footer (id_footer, logo, id_user, status, updated_at) FROM stdin;
1	uploads/branding/693e5c5ddc75e_1765694557.png	1	active	2025-12-14 13:42:37.913853
\.


--
-- TOC entry 3593 (class 0 OID 61960)
-- Dependencies: 246
-- Data for Name: galeri; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.galeri (id_galeri, gambar, judul, deskripsi, filter_kategori, id_user, status, created_at) FROM stdin;
3	galeri_1764811949.jpeg	Praktikum Machine Learning	Mahasiswa sedang melakukan praktikum machine learning menggunakan Python dan TensorFlow	\N	1	active	2025-12-04 08:32:29.998691
4	galeri_1765275289.jpeg	Workshop Data Science	Workshop data science bersama praktisi industri tentang big data analytics	\N	1	active	2025-12-09 17:14:49.068411
5	galeri_1765275311.jpeg	Penelitian Kolaboratif	Tim peneliti lab sedang melakukan riset kolaboratif dengan industri teknologi	\N	1	active	2025-12-09 17:15:11.048511
\.


--
-- TOC entry 3576 (class 0 OID 61804)
-- Dependencies: 229
-- Data for Name: kontak; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.kontak (id_kontak, whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, id_user, status, updated_at) FROM stdin;
3	085334772234	labdt@gmail.com	Jl. Soekarno Hatta No.9, Mojolangu, Kec. Lowokwaru, Jawa Barat, Jawa Timur 65141	https://quilljs.com/docs/quickstar	Senin-Jumat 18.00	https://quilljs.com/docs/quickstart	https://quilljs.com/docs/quickstart	https://quilljs.com/docs/quickstart	<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d830.7070012634871!2d112.61449257007301!3d-7.943979990524722!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd629dfd58aaf95%3A0xe72a182dfd18e01c!2sGedung%20Teknik%20Sipil%2C%20Teknik%20Informatika%20%26%20Magister%20Terapan%2C%20POLITEKNIK%20NEGERI%20MALANG!5e0!3m2!1sid!2sid!4v1764818034237!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';	2	active	2025-12-10 22:37:31.80284
\.


--
-- TOC entry 3589 (class 0 OID 61924)
-- Dependencies: 242
-- Data for Name: konten; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.konten (id_konten, kategori_konten, judul, slug, isi, gambar, tanggal_posting, id_user, status) FROM stdin;
4	Agenda	Keseharian Zaki	keseharian-zaki	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.\r\n\r\nDonec a lacus lorem. Donec gravida quis lacus sed cursus. Vestibulum ut volutpat ligula, ac dictum urna. Donec elementum erat massa, eu interdum nunc semper quis. Fusce eu scelerisque ipsum, ac efficitur eros. Aenean libero enim, vehicula et lorem nec, interdum posuere dui. Vivamus sem mi, faucibus ac fermentum sit amet, pretium et magna. Donec nec varius risus. Nulla in orci risus. Cras at lorem et mi tincidunt tempus. Morbi quis turpis et ex ultricies mollis in viverra dolor. Fusce cursus enim libero, nec ullamcorper velit interdum ut. Curabitur facilisis mollis urna, ut tempor orci interdum vel. Cras tincidunt aliquam hendrerit.\r\n\r\nMauris volutpat ultrices massa, at congue tortor suscipit consequat. Aenean volutpat sapien ac mi semper ultricies. Ut malesuada laoreet augue eu consequat. Morbi faucibus tellus euismod nibh rutrum congue. Etiam venenatis libero ac est lacinia, a tempor turpis tincidunt. Sed et tellus aliquet, tempus lorem vitae, tristique urna. Curabitur ac dapibus nisl, nec aliquet nibh. Fusce vehicula, diam eget finibus molestie, lectus nisi efficitur urna, nec dapibus sem ante at metus. Vestibulum eu malesuada ipsum, ut iaculis elit. Fusce et lobortis nibh. Aenean viverra cursus metus eu mollis.\r\n\r\nSed blandit magna eget turpis feugiat viverra. Etiam semper faucibus urna, at hendrerit enim fermentum vitae. Morbi ultricies consequat pretium. Etiam non mollis eros. Aliquam rutrum massa ac tincidunt faucibus. Sed fringilla, ligula id porttitor dapibus, purus neque consectetur purus, non porta eros sem eu mi. Quisque sed tellus ultricies metus sodales posuere. Nulla ornare eleifend velit ac sollicitudin. Mauris ultrices arcu eget nibh aliquam malesuada. Integer aliquet eu risus sit amet dapibus. Sed quis tellus non elit porta finibus. Fusce sodales mattis arcu, ut mattis ante maximus aliquam. Nam mollis egestas augue, in auctor diam tempor auctor. Proin dapibus ac nulla id iaculis. Vestibulum molestie sit amet magna consequat viverra.\r\n\r\nSed molestie diam at felis maximus blandit. Proin sit amet faucibus velit. Donec ac pharetra mi. Proin sit amet sodales orci, a viverra sapien. Aliquam accumsan nulla sit amet est pharetra molestie nec non sapien. Proin arcu erat, feugiat a porttitor non, dapibus eu ipsum. Donec eget interdum felis, id commodo tortor. Nulla efficitur malesuada magna a sagittis. Aliquam eu felis laoreet, aliquet libero sit amet, aliquam sapien. In a velit erat. Nam ultrices sed elit sed sollicitudin.	konten_1764816401.jpeg	2025-12-04 09:46:41.518628	1	active
6	Berita	Wakwaw	wakwaw	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.\r\n\r\nDonec a lacus lorem. Donec gravida quis lacus sed cursus. Vestibulum ut volutpat ligula, ac dictum urna. Donec elementum erat massa, eu interdum nunc semper quis. Fusce eu scelerisque ipsum, ac efficitur eros. Aenean libero enim, vehicula et lorem nec, interdum posuere dui. Vivamus sem mi, faucibus ac fermentum sit amet, pretium et magna. Donec nec varius risus. Nulla in orci risus. Cras at lorem et mi tincidunt tempus. Morbi quis turpis et ex ultricies mollis in viverra dolor. Fusce cursus enim libero, nec ullamcorper velit interdum ut. Curabitur facilisis mollis urna, ut tempor orci interdum vel. Cras tincidunt aliquam hendrerit.\r\n\r\nMauris volutpat ultrices massa, at congue tortor suscipit consequat. Aenean volutpat sapien ac mi semper ultricies. Ut malesuada laoreet augue eu consequat. Morbi faucibus tellus euismod nibh rutrum congue. Etiam venenatis libero ac est lacinia, a tempor turpis tincidunt. Sed et tellus aliquet, tempus lorem vitae, tristique urna. Curabitur ac dapibus nisl, nec aliquet nibh. Fusce vehicula, diam eget finibus molestie, lectus nisi efficitur urna, nec dapibus sem ante at metus. Vestibulum eu malesuada ipsum, ut iaculis elit. Fusce et lobortis nibh. Aenean viverra cursus metus eu mollis.\r\n\r\nSed blandit magna eget turpis feugiat viverra. Etiam semper faucibus urna, at hendrerit enim fermentum vitae. Morbi ultricies consequat pretium. Etiam non mollis eros. Aliquam rutrum massa ac tincidunt faucibus. Sed fringilla, ligula id porttitor dapibus, purus neque consectetur purus, non porta eros sem eu mi. Quisque sed tellus ultricies metus sodales posuere. Nulla ornare eleifend velit ac sollicitudin. Mauris ultrices arcu eget nibh aliquam malesuada. Integer aliquet eu risus sit amet dapibus. Sed quis tellus non elit porta finibus. Fusce sodales mattis arcu, ut mattis ante maximus aliquam. Nam mollis egestas augue, in auctor diam tempor auctor. Proin dapibus ac nulla id iaculis. Vestibulum molestie sit amet magna consequat viverra.\r\n\r\nSed molestie diam at felis maximus blandit. Proin sit amet faucibus velit. Donec ac pharetra mi. Proin sit amet sodales orci, a viverra sapien. Aliquam accumsan nulla sit amet est pharetra molestie nec non sapien. Proin arcu erat, feugiat a porttitor non, dapibus eu ipsum. Donec eget interdum felis, id commodo tortor. Nulla efficitur malesuada magna a sagittis. Aliquam eu felis laoreet, aliquet libero sit amet, aliquam sapien. In a velit erat. Nam ultrices sed elit sed sollicitudin.	konten_1765370674.jpeg	2025-12-10 19:44:34.208338	2	active
5	Pengumuman	Keseharian Zinki	keseharian-zinki	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.\r\n\r\nDonec a lacus lorem. Donec gravida quis lacus sed cursus. Vestibulum ut volutpat ligula, ac dictum urna. Donec elementum erat massa, eu interdum nunc semper quis. Fusce eu scelerisque ipsum, ac efficitur eros. Aenean libero enim, vehicula et lorem nec, interdum posuere dui. Vivamus sem mi, faucibus ac fermentum sit amet, pretium et magna. Donec nec varius risus. Nulla in orci risus. Cras at lorem et mi tincidunt tempus. Morbi quis turpis et ex ultricies mollis in viverra dolor. Fusce cursus enim libero, nec ullamcorper velit interdum ut. Curabitur facilisis mollis urna, ut tempor orci interdum vel. Cras tincidunt aliquam hendrerit.\r\n\r\nMauris volutpat ultrices massa, at congue tortor suscipit consequat. Aenean volutpat sapien ac mi semper ultricies. Ut malesuada laoreet augue eu consequat. Morbi faucibus tellus euismod nibh rutrum congue. Etiam venenatis libero ac est lacinia, a tempor turpis tincidunt. Sed et tellus aliquet, tempus lorem vitae, tristique urna. Curabitur ac dapibus nisl, nec aliquet nibh. Fusce vehicula, diam eget finibus molestie, lectus nisi efficitur urna, nec dapibus sem ante at metus. Vestibulum eu malesuada ipsum, ut iaculis elit. Fusce et lobortis nibh. Aenean viverra cursus metus eu mollis.\r\n\r\nSed blandit magna eget turpis feugiat viverra. Etiam semper faucibus urna, at hendrerit enim fermentum vitae. Morbi ultricies consequat pretium. Etiam non mollis eros. Aliquam rutrum massa ac tincidunt faucibus. Sed fringilla, ligula id porttitor dapibus, purus neque consectetur purus, non porta eros sem eu mi. Quisque sed tellus ultricies metus sodales posuere. Nulla ornare eleifend velit ac sollicitudin. Mauris ultrices arcu eget nibh aliquam malesuada. Integer aliquet eu risus sit amet dapibus. Sed quis tellus non elit porta finibus. Fusce sodales mattis arcu, ut mattis ante maximus aliquam. Nam mollis egestas augue, in auctor diam tempor auctor. Proin dapibus ac nulla id iaculis. Vestibulum molestie sit amet magna consequat viverra.\r\n\r\nSed molestie diam at felis maximus blandit. Proin sit amet faucibus velit. Donec ac pharetra mi. Proin sit amet sodales orci, a viverra sapien. Aliquam accumsan nulla sit amet est pharetra molestie nec non sapien. Proin arcu erat, feugiat a porttitor non, dapibus eu ipsum. Donec eget interdum felis, id commodo tortor. Nulla efficitur malesuada magna a sagittis. Aliquam eu felis laoreet, aliquet libero sit amet, aliquam sapien. In a velit erat. Nam ultrices sed elit sed sollicitudin.	konten_1765370642.jpeg	2025-12-10 19:44:02.901885	2	active
7	Agenda	Buku harian zaki	buku-harian-zaki	-	konten_1765706950.jpeg	2025-12-14 17:09:10.092782	1	active
\.


--
-- TOC entry 3568 (class 0 OID 61740)
-- Dependencies: 221
-- Data for Name: misi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.misi (id_misi, isi_misi, urutan, id_user, status, created_at) FROM stdin;
4	Melakukan penelitian berkualitas tinggi yang berkontribusi pada kemajuan ilmu pengetahuan dan teknologi di bidang data, selaras dengan agenda riset JTI Polinema.	2	1	active	2025-12-09 10:15:26.128803
5	Mengembangkan inovasi teknologi data yang dapat diterapkan dalam dunia industri, pendidikan, dan pemerintahan guna meningkatkan daya saing lulusan JTI Polinema.	3	1	active	2025-12-09 10:15:38.844051
6	Membangun infrastruktur dan sistem data yang skalabel dan efisien untuk mendukung kebutuhan analitik, kecerdasan buatan, dan Big Data, serta memperkuat keunggulan akademik JTI Polinema.	4	1	active	2025-12-09 10:15:47.978642
7	Menjalin kolaborasi dengan akademisi, industri, dan pemerintah dalam pengembangan solusi teknologi data yang inovatif, sejalan dengan misi JTI Polinema dalam memperkuat sinergi dengan dunia kerja.	5	1	active	2025-12-09 10:15:57.896471
8	Meningkatkan kapasitas dan kompetensi sumber daya manusia di lingkungan JTI Polinema melalui pelatihan, penelitian, seminar, dan publikasi ilmiah di bidang teknologi data.	6	1	active	2025-12-09 10:16:08.290059
9	Menyediakan layanan dan rekomendasi berbasis riset bagi JTI Polinema serta mitra industri dan akademik untuk mengoptimalkan pengelolaan dan pemanfaatan data.	7	1	active	2025-12-09 10:16:20.988649
12	Menjaga etika dan keamanan data dalam setiap penelitian dan pengembangan teknologi, mendukung prinsip tata kelola data yang baik dalam lingkungan akademik dan industri.	7	1	active	2025-12-09 10:30:16.575513
14	Mengembangkan praktik riset dan infrastruktur teknologi data yang berkelanjutan melalui penerapan prinsip efisiensi energi, optimalisasi sumber daya, serta pengelolaan siklus hidup data yang ramah lingkungan.	7	1	active	2025-12-09 10:30:45.715353
3	Mendukung visi dan misi Jurusan Teknologi Informasi Polinema Melalui penelitian dan pengembangan di bidang penyimpanan, pengolahan, serta rekayasa sistem data.	1	1	active	2025-12-09 10:15:06.153037
16	Mendukung visi dan misi Jurusan Teknologi Informasi Polinema Melalui penelitian dan pengembangan di bidang penyimpanan, pengolahan, serta rekayasa sistem data.	1	2	active	2025-12-10 20:09:50.161717
15	Mengembangkan praktik riset dan infrastruktur teknologi data yang berkelanjutan melalui penerapan prinsip efisiensi energi, optimalisasi sumber daya, serta pengelolaan siklus hidup data yang ramah lingkungan. 	7	2	active	2025-12-10 08:59:15.511565
\.


--
-- TOC entry 3572 (class 0 OID 61774)
-- Dependencies: 225
-- Data for Name: navbar; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.navbar (id_nav, logo, id_user, status, updated_at) FROM stdin;
2	uploads/branding/693e5c80730af_1765694592.png	2	active	2025-12-14 13:43:26.838556
\.


--
-- TOC entry 3586 (class 0 OID 61891)
-- Dependencies: 239
-- Data for Name: publikasi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.publikasi (id_publikasi, judul, cover, abstrak, tahun, jurnal, file_path, link_shinta, tanggal_publikasi, id_user, status, created_at) FROM stdin;
5	Zaki	cover_1765296001.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	2025	jhoiytghj	pub_1764823150.pdf	https://www.lipsum.com/feed/html	2025-12-04	2	active	2025-12-04 11:39:10.798306
2	Buku harian zaki	cover_1765371279.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	2025	Daily life zaki	pub_1764810997.pdf	https://sinta.kemdiktisaintek.go.id/	2025-12-11	2	active	2025-12-04 08:16:37.244256
6	Buku harian zaki	cover_1765371502.jpeg	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	2025	Daily life zaki	pub_1765371502.pdf	https://sinta.kemdiktisaintek.go.id/	2025-12-10	2	active	2025-12-10 19:58:22.68539
\.


--
-- TOC entry 3587 (class 0 OID 61907)
-- Dependencies: 240
-- Data for Name: publikasi_anggota; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.publikasi_anggota (id_publikasi, id_anggota, urutan_penulis) FROM stdin;
5	2	1
2	4	1
6	2	1
\.


--
-- TOC entry 3595 (class 0 OID 61977)
-- Dependencies: 248
-- Data for Name: riwayat_pengajuan; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.riwayat_pengajuan (id_riwayat, tabel_sumber, id_data, id_operator, id_admin, status_lama, status_baru, catatan, created_at) FROM stdin;
1	anggota_lab	1	\N	1	\N	active	Tambah anggota: Zaki	2025-12-03 17:03:10.226006
3	anggota_lab	2	\N	1	\N	active	Tambah anggota: pembunuh gila	2025-12-03 17:06:27.610901
4	struktur_lab	1	\N	1	\N	active	Tambah struktur: Ketua Geng	2025-12-03 17:06:49.536363
6	publikasi	1	\N	1	\N	active	Tambah publikasi: Buku harian zaki	2025-12-03 17:08:43.103626
7	publikasi	1	\N	1	active	deleted	Hapus publikasi: Buku harian zaki	2025-12-03 17:09:00.752176
8	fasilitas	1	\N	1	\N	active	Tambah fasilitas: Laptop	2025-12-03 17:09:31.875606
9	fasilitas	1	\N	1	active	deleted	Hapus fasilitas: Laptop	2025-12-03 17:09:53.466183
10	galeri	1	\N	1	active	deleted	Hapus foto: Mencoba kehidupan zaki	2025-12-03 17:10:32.784746
11	konten	1	\N	1	\N	active	Tambah konten: Ulang tahun zaki	2025-12-03 17:11:07.559648
12	konten	1	\N	1	active	deleted	Hapus konten: Ulang tahun zaki	2025-12-03 17:11:34.139396
13	tentang_kami	1	\N	1	\N	active	Tambah profil lab	2025-12-03 17:12:24.822851
14	visi	1	\N	1	\N	\N	Tambah visi	2025-12-03 17:12:35.066582
15	misi	1	\N	1	\N	\N	Tambah misi #1	2025-12-03 17:13:05.964564
16	kontak	1	\N	1	\N	active	Tambah info kontak	2025-12-03 17:19:59.532246
17	struktur_lab	2	\N	1	\N	active	Tambah struktur: Ketua Lab	2025-12-03 20:43:25.23897
18	publikasi	2	\N	1	\N	active	Tambah publikasi: Buku harian zaki	2025-12-04 08:16:37.244256
19	fasilitas	2	\N	1	\N	active	Tambah fasilitas: Buku harian zaki	2025-12-04 08:17:26.622242
20	fasilitas	3	\N	1	\N	active	Tambah fasilitas: Buku harian zaki	2025-12-04 08:26:49.525889
21	fasilitas	3	\N	1	active	deleted	Hapus fasilitas: Buku harian zaki	2025-12-04 08:26:51.900711
22	fasilitas	2	\N	1	active	deleted	Hapus fasilitas: Buku harian zaki	2025-12-04 08:26:53.903515
23	galeri	3	\N	1	\N	active	Tambah foto: Makan Makan	2025-12-04 08:32:30.010867
24	galeri	2	\N	1	active	deleted	Hapus foto: Makan Makan	2025-12-04 08:32:33.847303
25	fasilitas	4	\N	1	\N	active	Tambah fasilitas: Laptop	2025-12-04 08:33:00.08919
26	konten	2	\N	1	\N	active	Tambah konten: Buku harian zaki	2025-12-04 09:01:59.922523
27	konten	2	\N	1	active	deleted	Hapus konten: Buku harian zaki	2025-12-04 09:04:38.717183
28	konten	4	\N	1	\N	active	Tambah konten: Keseharian Zaki	2025-12-04 09:46:41.536086
29	publikasi	3	\N	1	\N	active	Tambah publikasi: gdugf	2025-12-04 11:24:20.294677
30	publikasi	3	\N	1	active	deleted	Hapus publikasi: gdugf	2025-12-04 11:24:42.125174
31	publikasi	3	\N	1	\N	deleted	Hapus publikasi: 	2025-12-04 11:29:09.648159
32	publikasi	3	\N	1	\N	deleted	Hapus publikasi: 	2025-12-04 11:33:24.662453
33	publikasi	4	\N	1	\N	active	Tambah publikasi: djfsiue	2025-12-04 11:33:24.673166
34	publikasi	4	\N	1	active	deleted	Hapus publikasi: djfsiue	2025-12-04 11:34:47.394386
35	publikasi	5	2	1	\N	active	Tambah publikasi: iuriuawrhcvur	2025-12-04 11:39:10.798306
36	misi	2	\N	1	\N	\N	Tambah misi #2	2025-12-09 09:59:52.489719
37	visi	1	\N	1	active	deleted	Hapus visi	2025-12-09 10:14:33.163308
38	visi	2	\N	1	\N	\N	Tambah visi	2025-12-09 10:14:38.16174
39	visi	1	\N	1	\N	deleted	Hapus visi	2025-12-09 10:14:38.172412
40	misi	1	\N	1	active	deleted	Hapus misi	2025-12-09 10:14:58.224308
41	misi	2	\N	1	active	deleted	Hapus misi	2025-12-09 10:15:01.127606
42	misi	3	\N	1	\N	\N	Tambah misi #1	2025-12-09 10:15:06.190671
43	misi	2	\N	1	\N	deleted	Hapus misi	2025-12-09 10:15:06.196523
44	misi	4	\N	1	\N	\N	Tambah misi #2	2025-12-09 10:15:26.167169
45	misi	2	\N	1	\N	deleted	Hapus misi	2025-12-09 10:15:26.173396
46	misi	5	\N	1	\N	\N	Tambah misi #3	2025-12-09 10:15:38.881752
47	misi	2	\N	1	\N	deleted	Hapus misi	2025-12-09 10:15:38.890864
48	misi	6	\N	1	\N	\N	Tambah misi #4	2025-12-09 10:15:48.019762
49	misi	2	\N	1	\N	deleted	Hapus misi	2025-12-09 10:15:48.02733
50	misi	7	\N	1	\N	\N	Tambah misi #5	2025-12-09 10:15:57.93623
51	misi	2	\N	1	\N	deleted	Hapus misi	2025-12-09 10:15:57.941852
52	misi	8	\N	1	\N	\N	Tambah misi #6	2025-12-09 10:16:08.329503
53	misi	2	\N	1	\N	deleted	Hapus misi	2025-12-09 10:16:08.338218
54	misi	9	\N	1	\N	\N	Tambah misi #7	2025-12-09 10:16:21.025698
55	misi	2	\N	1	\N	deleted	Hapus misi	2025-12-09 10:16:21.034129
56	misi	10	\N	1	\N	active	Tambah misi #7	2025-12-09 10:21:26.124946
57	misi	2	\N	1	active	deleted	Hapus misi	2025-12-09 10:21:26.130821
58	misi	11	\N	1	\N	active	Tambah misi #7	2025-12-09 10:26:29.107159
59	misi	2	\N	1	active	deleted	Hapus misi	2025-12-09 10:26:29.114839
60	misi	12	\N	1	\N	active	Tambah misi #7	2025-12-09 10:30:16.611791
61	misi	2	\N	1	active	deleted	Hapus misi	2025-12-09 10:30:16.619263
62	misi	13	\N	1	\N	active	Tambah misi #7	2025-12-09 10:30:21.491276
63	misi	2	\N	1	active	deleted	Hapus misi	2025-12-09 10:30:21.498813
64	misi	14	\N	1	\N	active	Tambah misi #7	2025-12-09 10:30:45.755309
65	misi	2	\N	1	active	deleted	Hapus misi	2025-12-09 10:30:45.762214
66	misi	10	\N	1	active	deleted	Hapus misi	2025-12-09 10:31:44.830781
67	misi	11	\N	1	active	deleted	Hapus misi	2025-12-09 10:31:51.690696
68	misi	11	\N	1	active	deleted	Hapus misi	2025-12-09 10:32:33.737852
69	misi	11	\N	1	active	deleted	Hapus misi	2025-12-09 10:32:47.378406
70	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:32:51.72659
71	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:33:11.780428
72	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:36:37.130179
73	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:46:51.932381
74	sejarah	1	\N	1	\N	active	Tambah roadmap: Roadmap Jangka Pendek	2025-12-09 10:54:52.715521
75	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:54:52.720707
76	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:55:15.677173
77	sejarah	2	\N	1	\N	active	Tambah roadmap: Tahap Jangka Menengah	2025-12-09 10:57:30.59828
78	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:57:30.604832
79	sejarah	3	\N	1	\N	active	Tambah roadmap: Tahap Jangka Panjang	2025-12-09 10:59:42.040847
80	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:59:42.046659
81	misi	13	\N	1	active	deleted	Hapus misi	2025-12-09 10:59:56.987517
82	galeri	4	\N	1	\N	active	Tambah foto: Workshop Data Science	2025-12-09 17:14:49.096495
83	galeri	5	\N	1	\N	active	Tambah foto: Penelitian Kolaboratif	2025-12-09 17:15:11.05889
2	anggota_lab	1	\N	1	active	active	Hapus anggota: Zaki	2025-12-03 17:04:26.619235
84	fasilitas	5	\N	1	\N	active	Tambah fasilitas: Lab Utama Data Technology	2025-12-09 17:35:18.871071
85	fasilitas	6	\N	1	\N	active	Tambah fasilitas: MySQL & PostgreSQL	2025-12-09 17:36:41.027428
88	publikasi	5	\N	1	pending	active	Update publikasi: iuriuawrhcvur	2025-12-09 22:59:42.99933
89	publikasi	2	\N	1	pending	rejected	Reject publikasi: Buku harian zaki	2025-12-09 23:00:09.71456
91	tentang_kami	2	\N	1	pending	rejected	Reject profil	2025-12-10 08:00:27.76374
95	anggota_lab	3	\N	1	pending	active	DISETUJUI anggota: mamad	2025-12-10 13:12:28.614896
5	struktur_lab	1	\N	1	active	rejected	Hapus struktur: Ketua 	2025-12-03 17:07:14.350666
94	anggota_lab	2	2	1	active	active	Update anggota: zaki	2025-12-10 12:27:46.221954
93	anggota_lab	3	2	1	\N	rejected	Tambah anggota: mamad	2025-12-10 12:27:33.199272
92	slider	1	2	1	\N	active	Tambah slider: Galeri	2025-12-10 10:43:31.066553
90	publikasi	5	2	1	active	active	Edit publikasi: Zaki	2025-12-10 00:10:46.009441
87	publikasi	2	2	1	active	active	Edit publikasi: Buku harian zaki	2025-12-09 21:17:46.141924
86	publikasi	5	2	1	active	active	Edit publikasi: iuriuawrhcvur	2025-12-09 21:17:36.735263
97	anggota_lab	4	\N	1	pending	active	DISETUJUI anggota: rafiqi	2025-12-10 15:12:05.491194
100	konten	4	2	\N	active	pending	Update konten: Keseharian Zaki	2025-12-10 19:44:09.484633
101	konten	6	2	\N	\N	pending	Tambah konten: Wakwaw	2025-12-10 19:44:34.222735
102	galeri	6	2	\N	\N	pending	Tambah foto: Penelitian Kolaboratif	2025-12-10 19:46:12.510957
103	galeri	6	2	\N	pending	deleted	Hapus foto: Penelitian Kolaboratif	2025-12-10 19:46:34.043248
104	galeri	5	2	\N	active	pending	Update foto: Penelitian Kolaboratif	2025-12-10 19:46:53.888549
105	anggota_lab	3	2	\N	rejected	pending	Update anggota: mamad	2025-12-10 19:53:13.090475
106	anggota_lab	2	2	\N	active	pending	Update anggota: zaki	2025-12-10 19:53:20.012621
107	anggota_lab	4	2	\N	active	pending	Update anggota: rafiqi	2025-12-10 19:53:23.765449
108	publikasi	5	2	\N	active	pending	Edit publikasi: Zaki	2025-12-10 19:54:09.103462
109	publikasi	2	2	\N	active	pending	Edit publikasi: Buku harian zaki	2025-12-10 19:54:31.735572
110	publikasi	2	2	\N	pending	pending	Edit publikasi: Buku harian zaki	2025-12-10 19:54:39.127885
111	fasilitas	7	2	\N	\N	pending	Tambah fasilitas: MySQL & PostgreSQL	2025-12-10 19:55:14.673478
112	publikasi	6	2	\N	\N	pending	Tambah publikasi: Buku harian zaki	2025-12-10 19:58:22.68539
113	fasilitas	8	2	\N	\N	pending	Tambah fasilitas: MySQL & PostgreSQL	2025-12-10 19:59:36.006502
114	fasilitas	9	2	\N	\N	pending	Tambah fasilitas: Lab Utama Data Technology	2025-12-10 20:05:39.69722
115	fasilitas	10	2	\N	\N	pending	Tambah fasilitas: Lab Utama Data Technology	2025-12-10 20:06:46.641844
116	fasilitas	11	2	\N	\N	pending	Pengajuan edit fasilitas (dari ID: 4): Laptop	2025-12-10 20:07:03.421833
117	fasilitas	12	2	\N	\N	pending	Pengajuan edit fasilitas (dari ID: 4): Laptop	2025-12-10 20:07:12.729337
118	slider	2	2	\N	\N	pending	Tambah slider: Tahap Jangka Panjang	2025-12-10 20:10:56.826373
119	slider	3	2	\N	\N	pending	Tambah slider: Tahap Jangka Panjang	2025-12-10 20:11:11.904402
122	anggota_lab	4	\N	1	pending	active	DISETUJUI anggota: rafiqi	2025-12-10 21:01:08.920825
123	anggota_lab	3	\N	1	pending	active	DISETUJUI anggota: mamad	2025-12-10 21:01:11.341898
124	anggota_lab	2	\N	1	pending	active	DISETUJUI anggota: zaki	2025-12-10 21:01:16.94761
125	struktur_lab	3	\N	1	\N	active	Tambah struktur: Member	2025-12-10 21:02:27.917057
126	struktur_lab	4	\N	1	\N	active	Tambah struktur: Member	2025-12-10 21:02:43.460405
127	slider	1	\N	1	pending	active	DISETUJUI slider: Galeri	2025-12-10 21:18:28.843622
128	slider	2	\N	1	pending	active	DISETUJUI slider: Tahap Jangka Panjang	2025-12-10 21:18:31.095506
129	slider	3	\N	1	pending	active	DISETUJUI slider: Tahap Jangka Panjang	2025-12-10 21:18:33.314683
130	konten	6	\N	1	pending	active	DISETUJUI konten: Wakwaw	2025-12-10 21:20:53.857155
131	konten	5	\N	1	pending	active	DISETUJUI konten: Keseharian Zinki	2025-12-10 21:20:55.706524
132	konten	4	\N	1	pending	active	DISETUJUI konten: Keseharian Zaki	2025-12-10 21:20:57.439116
133	galeri	5	\N	1	pending	active	DISETUJUI foto: Penelitian Kolaboratif	2025-12-10 21:24:13.473116
134	fasilitas	12	\N	1	pending	active	DISETUJUI fasilitas: Laptop	2025-12-10 21:44:38.214926
135	fasilitas	11	\N	1	pending	active	DISETUJUI fasilitas: Laptop	2025-12-10 22:01:34.926255
136	fasilitas	4	\N	1	pending	rejected	DITOLAK fasilitas: Laptop	2025-12-10 22:01:42.965904
137	fasilitas	10	\N	1	pending	active	DISETUJUI fasilitas: Lab Utama Data Technology	2025-12-10 22:02:15.370537
138	fasilitas	7	\N	1	pending	rejected	DITOLAK fasilitas: MySQL & PostgreSQL	2025-12-10 22:02:18.303052
139	fasilitas	9	\N	1	pending	active	DISETUJUI fasilitas: Lab Utama Data Technology	2025-12-10 22:02:26.167994
140	fasilitas	8	\N	1	pending	active	DISETUJUI fasilitas: MySQL & PostgreSQL	2025-12-10 22:02:28.847213
141	fasilitas	7	\N	1	rejected	active	Update fasilitas: MySQL & PostgreSQL	2025-12-10 22:02:37.259134
142	fasilitas	4	\N	1	rejected	active	Update fasilitas: Laptop	2025-12-10 22:02:40.948382
143	publikasi	6	\N	1	pending	rejected	DITOLAK publikasi: Buku harian zaki	2025-12-10 22:36:42.345354
144	publikasi	5	\N	1	pending	active	DISETUJUI publikasi: Zaki	2025-12-10 22:36:45.208028
145	publikasi	2	\N	1	pending	active	DISETUJUI publikasi: Buku harian zaki	2025-12-10 22:36:47.88717
146	publikasi	6	\N	1	rejected	active	Update publikasi: Buku harian zaki	2025-12-11 09:24:25.782543
121	fasilitas	4	2	1	pending	active	Update fasilitas: Laptop	2025-12-10 20:15:35.699665
120	fasilitas	4	2	1	active	active	Update fasilitas: Laptop	2025-12-10 20:14:30.863043
98	slider	1	2	1	active	active	Update slider: Galeri	2025-12-10 19:41:01.842284
96	anggota_lab	4	2	1	\N	active	Tambah anggota: rafiqi	2025-12-10 15:11:43.785172
99	konten	5	2	1	\N	active	Tambah konten: Keseharian Zinki	2025-12-10 19:44:02.929849
147	struktur_lab	5	\N	1	\N	active	Tambah struktur: Member	2025-12-14 16:38:18.514692
148	anggota_lab	2	\N	1	active	active	Edit anggota: zaki	2025-12-14 16:38:54.078658
149	anggota_lab	2	\N	1	active	active	Edit anggota: zaki	2025-12-14 16:39:15.029826
150	konten	7	\N	1	\N	active	Tambah konten: Buku harian zaki	2025-12-14 17:09:10.102253
151	anggota_lab	2	\N	1	active	active	Edit anggota: zaki	2025-12-14 17:29:08.06253
152	anggota_lab	3	\N	1	active	active	Edit anggota: mamad	2025-12-14 17:29:13.303877
153	anggota_lab	2	\N	1	active	active	Edit anggota: zaki	2025-12-14 17:29:28.16215
154	anggota_lab	4	\N	1	active	active	Edit anggota: rafiqi	2025-12-14 17:29:33.687756
155	anggota_lab	4	2	\N	active	pending	Update anggota: rafiqi	2025-12-14 18:26:10.084128
156	struktur_lab	3	2	\N	active	pending	Update struktur: Member	2025-12-14 18:26:22.710643
\.


--
-- TOC entry 3570 (class 0 OID 61757)
-- Dependencies: 223
-- Data for Name: sejarah; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sejarah (id_sejarah, tahun, judul, deskripsi, urutan, id_user, status, created_at) FROM stdin;
2	2027–2030	Tahap Jangka Menengah	Pada tahap jangka menengah, laboratorium berfokus pada penguatan riset dan kolaborasi melalui pengadaan server GPU dan cluster big data untuk mendukung penelitian lanjutan. Produktivitas riset ditingkatkan lewat publikasi, hak cipta, dan prototipe inovasi. Laboratorium juga memperluas kerja sama dengan industri dan lembaga riset, sekaligus menerapkan skema sertifikasi data science, big data, dan AI bagi mahasiswa. Selain itu, dibangun mekanisme riset berkelanjutan berbasis CI/CD agar penelitian dapat diteruskan lintas angkatan secara lebih efisien.	2	1	active	2025-12-09 10:57:30.586474
3	2030–2035	Tahap Jangka Panjang	Pada tahap jangka panjang, Laboratorium Teknologi Data ditargetkan menjadi Center of Excellence di bidang data science, big data, dan AI. Pengembangannya mencakup pembangunan fasilitas berbasis HPC dan cloud-hybrid untuk riset berskala besar, serta perluasan kolaborasi internasional untuk meningkatkan kualitas publikasi dan inovasi. Laboratorium juga mendorong hilirisasi riset menjadi produk, startup, dan layanan publik berbasis data, sekaligus berperan sebagai mitra strategis pemerintah dan industri dalam solusi dan kebijakan berbasis data. Tujuan akhirnya adalah membentuk ekosistem riset dan inovasi yang kuat, berkelanjutan, dan berdampak luas.	1	1	active	2025-12-09 10:59:42.030912
1	2025–2027	Roadmap Jangka Pendek	Pada tahap awal, pengembangan laboratorium difokuskan pada penguatan operasional melalui penyusunan SOP untuk praktikum, penelitian, dan pengelolaan fasilitas. Penyediaan komputer, server dasar, serta pemanfaatan software open-source dan platform cloud dilakukan untuk mendukung pembelajaran dan riset Big Data maupun AI. Penguatan SDM dilaksanakan melalui workshop dan pelatihan internal. Selain itu, tahap ini mendorong penelitian bersama mahasiswa melalui tugas akhir, proyek mini, dan studi kasus untuk membangun budaya riset dan memperkuat posisi laboratorium.	3	1	active	2025-12-09 10:54:52.704387
\.


--
-- TOC entry 3578 (class 0 OID 61821)
-- Dependencies: 231
-- Data for Name: slider; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.slider (id_slider, gambar, judul, deskripsi, urutan, id_user, status, tanggal_dibuat) FROM stdin;
3	slider_1765372271.jpeg	Tahap Jangka Panjang	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	3	2	active	2025-12-10 20:11:11.890954
2	slider_1765419635.jpeg	Tahap Jangka Panjang	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum erat ante, hendrerit vel nunc vel, ornare facilisis lectus. Morbi tortor sem, porttitor eu ex eget, feugiat ullamcorper ante. Sed tellus risus, fermentum vitae efficitur a, congue eu eros. Maecenas fringilla at ligula et dapibus. Aenean non ligula euismod, ultrices libero sit amet, pretium nibh. Etiam nulla lectus, lacinia eget leo et, egestas malesuada dolor. Integer porttitor molestie ex quis ornare. Pellentesque pellentesque iaculis pretium.	2	2	active	2025-12-10 20:10:56.811768
1	slider_1765338211.jpeg	Galeri	loremipsum\r\n	1	2	active	2025-12-10 10:43:31.045084
\.


--
-- TOC entry 3582 (class 0 OID 61857)
-- Dependencies: 235
-- Data for Name: social_media_anggota; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.social_media_anggota (id_social, id_anggota, platform, url) FROM stdin;
19	3	linkedin	https://www.instagram.com/
20	3	scholar	https://www.instagram.com/
21	2	linkedin	https://quilljs.com/docs/quickstart
22	2	scholar	https://www.instagram.com/
25	4	linkedin	https://quilljs.com/docs/quickstart
26	4	scholar	https://quilljs.com/docs/quickstart
\.


--
-- TOC entry 3584 (class 0 OID 61870)
-- Dependencies: 237
-- Data for Name: struktur_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.struktur_lab (id_struktur, id_anggota, jabatan, urutan, id_user, status, created_at) FROM stdin;
2	2	Ketua Lab	1	1	active	2025-12-03 20:43:25.227725
4	3	Member	2	1	active	2025-12-10 21:02:43.447991
5	4	Member	2	1	active	2025-12-14 16:38:18.496702
3	4	Member	2	2	pending	2025-12-10 21:02:27.906711
\.


--
-- TOC entry 3564 (class 0 OID 61704)
-- Dependencies: 217
-- Data for Name: tentang_kami; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tentang_kami (id_profil, profil_lab, logo_lab, penjelasan_logo, id_user, status, created_at, updated_at) FROM stdin;
3	Unit penunjang akademik di Jurusan Teknologi Informasi yang berfokus pada kegiatan pembelajaran, penelitian, serta pengembangan keilmuan di bidang teknologi berbasis data. Laboratorium ini menyediakan fasilitas praktikum dan riset yang mendukung penguasaan pengetahuan serta keterampilan mahasiswa dalam pengolahan data, analisis big data, kecerdasan buatan, dan machine learning. Selain sebagai sarana praktikum, Laboratorium Teknologi Data juga berperan sebagai pusat penelitian dan pengembangan bagi dosen maupun mahasiswa.	logo_1765418502.png	Penjelasan Logo: Variasi Logo Laboratorium Akademik Keilmuan Teknologi Data. Filosofi yang terkandung dalam logo ini berkisar pada visi dan harapan dari seluruh elemen lab	2	active	2025-12-10 20:09:28.780712	2025-12-11 09:01:42.327857
\.


--
-- TOC entry 3562 (class 0 OID 61688)
-- Dependencies: 215
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id_user, username, password, nama, email, role, created_at) FROM stdin;
1	admin	0192023a7bbd73250516f069df18b500	Administrator	admin@labdt.ac.id	admin	2025-12-03 11:48:39.360936
2	operator	2407bd807d6ca01d1bcd766c730cec9a	Operator Lab	operator@labdt.ac.id	operator	2025-12-03 11:48:39.360936
\.


--
-- TOC entry 3566 (class 0 OID 61722)
-- Dependencies: 219
-- Data for Name: visi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.visi (id_visi, isi_visi, urutan, id_user, status, created_at) FROM stdin;
2	Menjadi organisasi riset terkemuka dalam penelitian maupun pengembangan untuk mendorong inovasi teknologi serta keilmuan di bidang penyimpanan, pengolahan, dan rekayasa sistem data yang berkelanjutan.	1	1	active	2025-12-09 10:14:38.120681
\.


--
-- TOC entry 3618 (class 0 OID 0)
-- Dependencies: 232
-- Name: anggota_lab_id_anggota_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.anggota_lab_id_anggota_seq', 4, true);


--
-- TOC entry 3619 (class 0 OID 0)
-- Dependencies: 243
-- Name: fasilitas_id_fasilitas_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fasilitas_id_fasilitas_seq', 12, true);


--
-- TOC entry 3620 (class 0 OID 0)
-- Dependencies: 226
-- Name: footer_id_footer_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.footer_id_footer_seq', 1, true);


--
-- TOC entry 3621 (class 0 OID 0)
-- Dependencies: 245
-- Name: galeri_id_galeri_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.galeri_id_galeri_seq', 6, true);


--
-- TOC entry 3622 (class 0 OID 0)
-- Dependencies: 228
-- Name: kontak_id_kontak_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.kontak_id_kontak_seq', 3, true);


--
-- TOC entry 3623 (class 0 OID 0)
-- Dependencies: 241
-- Name: konten_id_konten_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.konten_id_konten_seq', 7, true);


--
-- TOC entry 3624 (class 0 OID 0)
-- Dependencies: 220
-- Name: misi_id_misi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.misi_id_misi_seq', 16, true);


--
-- TOC entry 3625 (class 0 OID 0)
-- Dependencies: 224
-- Name: navbar_id_nav_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.navbar_id_nav_seq', 2, true);


--
-- TOC entry 3626 (class 0 OID 0)
-- Dependencies: 238
-- Name: publikasi_id_publikasi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.publikasi_id_publikasi_seq', 6, true);


--
-- TOC entry 3627 (class 0 OID 0)
-- Dependencies: 247
-- Name: riwayat_pengajuan_id_riwayat_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.riwayat_pengajuan_id_riwayat_seq', 156, true);


--
-- TOC entry 3628 (class 0 OID 0)
-- Dependencies: 222
-- Name: sejarah_id_sejarah_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sejarah_id_sejarah_seq', 7, true);


--
-- TOC entry 3629 (class 0 OID 0)
-- Dependencies: 230
-- Name: slider_id_slider_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.slider_id_slider_seq', 3, true);


--
-- TOC entry 3630 (class 0 OID 0)
-- Dependencies: 234
-- Name: social_media_anggota_id_social_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.social_media_anggota_id_social_seq', 26, true);


--
-- TOC entry 3631 (class 0 OID 0)
-- Dependencies: 236
-- Name: struktur_lab_id_struktur_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.struktur_lab_id_struktur_seq', 5, true);


--
-- TOC entry 3632 (class 0 OID 0)
-- Dependencies: 216
-- Name: tentang_kami_id_profil_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tentang_kami_id_profil_seq', 3, true);


--
-- TOC entry 3633 (class 0 OID 0)
-- Dependencies: 214
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_user_seq', 2, true);


--
-- TOC entry 3634 (class 0 OID 0)
-- Dependencies: 218
-- Name: visi_id_visi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visi_id_visi_seq', 3, true);


--
-- TOC entry 3368 (class 2606 OID 61850)
-- Name: anggota_lab anggota_lab_nip_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab
    ADD CONSTRAINT anggota_lab_nip_key UNIQUE (nip);


--
-- TOC entry 3370 (class 2606 OID 61848)
-- Name: anggota_lab anggota_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab
    ADD CONSTRAINT anggota_lab_pkey PRIMARY KEY (id_anggota);


--
-- TOC entry 3387 (class 2606 OID 61953)
-- Name: fasilitas fasilitas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fasilitas
    ADD CONSTRAINT fasilitas_pkey PRIMARY KEY (id_fasilitas);


--
-- TOC entry 3362 (class 2606 OID 61797)
-- Name: footer footer_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer
    ADD CONSTRAINT footer_pkey PRIMARY KEY (id_footer);


--
-- TOC entry 3389 (class 2606 OID 61970)
-- Name: galeri galeri_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri
    ADD CONSTRAINT galeri_pkey PRIMARY KEY (id_galeri);


--
-- TOC entry 3364 (class 2606 OID 61814)
-- Name: kontak kontak_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kontak
    ADD CONSTRAINT kontak_pkey PRIMARY KEY (id_kontak);


--
-- TOC entry 3383 (class 2606 OID 61934)
-- Name: konten konten_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten
    ADD CONSTRAINT konten_pkey PRIMARY KEY (id_konten);


--
-- TOC entry 3385 (class 2606 OID 61936)
-- Name: konten konten_slug_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten
    ADD CONSTRAINT konten_slug_key UNIQUE (slug);


--
-- TOC entry 3356 (class 2606 OID 61750)
-- Name: misi misi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.misi
    ADD CONSTRAINT misi_pkey PRIMARY KEY (id_misi);


--
-- TOC entry 3360 (class 2606 OID 61782)
-- Name: navbar navbar_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.navbar
    ADD CONSTRAINT navbar_pkey PRIMARY KEY (id_nav);


--
-- TOC entry 3380 (class 2606 OID 61912)
-- Name: publikasi_anggota publikasi_anggota_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi_anggota
    ADD CONSTRAINT publikasi_anggota_pkey PRIMARY KEY (id_publikasi, id_anggota);


--
-- TOC entry 3378 (class 2606 OID 61901)
-- Name: publikasi publikasi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi
    ADD CONSTRAINT publikasi_pkey PRIMARY KEY (id_publikasi);


--
-- TOC entry 3396 (class 2606 OID 61985)
-- Name: riwayat_pengajuan riwayat_pengajuan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan
    ADD CONSTRAINT riwayat_pengajuan_pkey PRIMARY KEY (id_riwayat);


--
-- TOC entry 3358 (class 2606 OID 61767)
-- Name: sejarah sejarah_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sejarah
    ADD CONSTRAINT sejarah_pkey PRIMARY KEY (id_sejarah);


--
-- TOC entry 3366 (class 2606 OID 61831)
-- Name: slider slider_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.slider
    ADD CONSTRAINT slider_pkey PRIMARY KEY (id_slider);


--
-- TOC entry 3373 (class 2606 OID 61863)
-- Name: social_media_anggota social_media_anggota_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_media_anggota
    ADD CONSTRAINT social_media_anggota_pkey PRIMARY KEY (id_social);


--
-- TOC entry 3375 (class 2606 OID 61879)
-- Name: struktur_lab struktur_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab
    ADD CONSTRAINT struktur_lab_pkey PRIMARY KEY (id_struktur);


--
-- TOC entry 3352 (class 2606 OID 61715)
-- Name: tentang_kami tentang_kami_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tentang_kami
    ADD CONSTRAINT tentang_kami_pkey PRIMARY KEY (id_profil);


--
-- TOC entry 3346 (class 2606 OID 61702)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 3348 (class 2606 OID 61698)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id_user);


--
-- TOC entry 3350 (class 2606 OID 61700)
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- TOC entry 3354 (class 2606 OID 61733)
-- Name: visi visi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visi
    ADD CONSTRAINT visi_pkey PRIMARY KEY (id_visi);


--
-- TOC entry 3371 (class 1259 OID 61998)
-- Name: idx_anggota_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_anggota_status ON public.anggota_lab USING btree (status);


--
-- TOC entry 3390 (class 1259 OID 62001)
-- Name: idx_galeri_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_galeri_status ON public.galeri USING btree (status);


--
-- TOC entry 3381 (class 1259 OID 62000)
-- Name: idx_konten_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_konten_status ON public.konten USING btree (status);


--
-- TOC entry 3376 (class 1259 OID 61999)
-- Name: idx_publikasi_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_publikasi_status ON public.publikasi USING btree (status);


--
-- TOC entry 3391 (class 1259 OID 62003)
-- Name: idx_riwayat_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_riwayat_admin ON public.riwayat_pengajuan USING btree (id_admin);


--
-- TOC entry 3392 (class 1259 OID 62005)
-- Name: idx_riwayat_created; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_riwayat_created ON public.riwayat_pengajuan USING btree (created_at);


--
-- TOC entry 3393 (class 1259 OID 62002)
-- Name: idx_riwayat_operator; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_riwayat_operator ON public.riwayat_pengajuan USING btree (id_operator);


--
-- TOC entry 3394 (class 1259 OID 62004)
-- Name: idx_riwayat_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_riwayat_status ON public.riwayat_pengajuan USING btree (status_baru);


--
-- TOC entry 3343 (class 1259 OID 61997)
-- Name: idx_users_role; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_role ON public.users USING btree (role);


--
-- TOC entry 3344 (class 1259 OID 61996)
-- Name: idx_users_username; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_username ON public.users USING btree (username);


--
-- TOC entry 3405 (class 2606 OID 61851)
-- Name: anggota_lab anggota_lab_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab
    ADD CONSTRAINT anggota_lab_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3413 (class 2606 OID 61954)
-- Name: fasilitas fasilitas_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fasilitas
    ADD CONSTRAINT fasilitas_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3402 (class 2606 OID 61798)
-- Name: footer footer_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer
    ADD CONSTRAINT footer_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3414 (class 2606 OID 61971)
-- Name: galeri galeri_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri
    ADD CONSTRAINT galeri_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3403 (class 2606 OID 61815)
-- Name: kontak kontak_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kontak
    ADD CONSTRAINT kontak_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3412 (class 2606 OID 61937)
-- Name: konten konten_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten
    ADD CONSTRAINT konten_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3399 (class 2606 OID 61751)
-- Name: misi misi_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.misi
    ADD CONSTRAINT misi_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3401 (class 2606 OID 61783)
-- Name: navbar navbar_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.navbar
    ADD CONSTRAINT navbar_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3410 (class 2606 OID 61918)
-- Name: publikasi_anggota publikasi_anggota_id_anggota_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi_anggota
    ADD CONSTRAINT publikasi_anggota_id_anggota_fkey FOREIGN KEY (id_anggota) REFERENCES public.anggota_lab(id_anggota) ON DELETE CASCADE;


--
-- TOC entry 3411 (class 2606 OID 61913)
-- Name: publikasi_anggota publikasi_anggota_id_publikasi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi_anggota
    ADD CONSTRAINT publikasi_anggota_id_publikasi_fkey FOREIGN KEY (id_publikasi) REFERENCES public.publikasi(id_publikasi) ON DELETE CASCADE;


--
-- TOC entry 3409 (class 2606 OID 61902)
-- Name: publikasi publikasi_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi
    ADD CONSTRAINT publikasi_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3415 (class 2606 OID 61991)
-- Name: riwayat_pengajuan riwayat_pengajuan_id_admin_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan
    ADD CONSTRAINT riwayat_pengajuan_id_admin_fkey FOREIGN KEY (id_admin) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3416 (class 2606 OID 61986)
-- Name: riwayat_pengajuan riwayat_pengajuan_id_operator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan
    ADD CONSTRAINT riwayat_pengajuan_id_operator_fkey FOREIGN KEY (id_operator) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3400 (class 2606 OID 61768)
-- Name: sejarah sejarah_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sejarah
    ADD CONSTRAINT sejarah_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3404 (class 2606 OID 61832)
-- Name: slider slider_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.slider
    ADD CONSTRAINT slider_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3406 (class 2606 OID 61864)
-- Name: social_media_anggota social_media_anggota_id_anggota_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_media_anggota
    ADD CONSTRAINT social_media_anggota_id_anggota_fkey FOREIGN KEY (id_anggota) REFERENCES public.anggota_lab(id_anggota) ON DELETE CASCADE;


--
-- TOC entry 3407 (class 2606 OID 61880)
-- Name: struktur_lab struktur_lab_id_anggota_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab
    ADD CONSTRAINT struktur_lab_id_anggota_fkey FOREIGN KEY (id_anggota) REFERENCES public.anggota_lab(id_anggota) ON DELETE CASCADE;


--
-- TOC entry 3408 (class 2606 OID 61885)
-- Name: struktur_lab struktur_lab_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab
    ADD CONSTRAINT struktur_lab_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3397 (class 2606 OID 61716)
-- Name: tentang_kami tentang_kami_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tentang_kami
    ADD CONSTRAINT tentang_kami_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3398 (class 2606 OID 61734)
-- Name: visi visi_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visi
    ADD CONSTRAINT visi_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


-- Completed on 2025-12-14 20:27:50

--
-- PostgreSQL database dump complete
--

\unrestrict e5qrXo13sH3fphBbihdebbgqnmOHJL6nCHKBdfjy3ZUnCwuz6pC7xJvtsc6I3TF

