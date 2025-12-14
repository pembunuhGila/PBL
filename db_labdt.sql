--
-- PostgreSQL database dump
--

\restrict 599szSiHcMjmrKIE92jYYX17xqqkH2jyb2yf0uJVFF4cvytmheZpo13foxV4QF6

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-12-14 14:36:35

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 233 (class 1259 OID 60849)
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
    pendidikan_terakhir character varying(255),
    bidang_keahlian text,
    tanggal_bergabung date,
    ruangan character varying(100),
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT anggota_lab_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.anggota_lab OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 60848)
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
-- TOC entry 3587 (class 0 OID 0)
-- Dependencies: 232
-- Name: anggota_lab_id_anggota_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.anggota_lab_id_anggota_seq OWNED BY public.anggota_lab.id_anggota;


--
-- TOC entry 246 (class 1259 OID 60971)
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
-- TOC entry 245 (class 1259 OID 60970)
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
-- TOC entry 3588 (class 0 OID 0)
-- Dependencies: 245
-- Name: fasilitas_id_fasilitas_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fasilitas_id_fasilitas_seq OWNED BY public.fasilitas.id_fasilitas;


--
-- TOC entry 227 (class 1259 OID 60800)
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
-- TOC entry 226 (class 1259 OID 60799)
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
-- TOC entry 3589 (class 0 OID 0)
-- Dependencies: 226
-- Name: footer_id_footer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.footer_id_footer_seq OWNED BY public.footer.id_footer;


--
-- TOC entry 248 (class 1259 OID 60988)
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
-- TOC entry 247 (class 1259 OID 60987)
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
-- TOC entry 3590 (class 0 OID 0)
-- Dependencies: 247
-- Name: galeri_id_galeri_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.galeri_id_galeri_seq OWNED BY public.galeri.id_galeri;


--
-- TOC entry 242 (class 1259 OID 60935)
-- Name: kategori; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.kategori (
    id_kategori integer NOT NULL,
    nama character varying(100) NOT NULL,
    slug character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.kategori OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 60934)
-- Name: kategori_id_kategori_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.kategori_id_kategori_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kategori_id_kategori_seq OWNER TO postgres;

--
-- TOC entry 3591 (class 0 OID 0)
-- Dependencies: 241
-- Name: kategori_id_kategori_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.kategori_id_kategori_seq OWNED BY public.kategori.id_kategori;


--
-- TOC entry 229 (class 1259 OID 60815)
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
-- TOC entry 228 (class 1259 OID 60814)
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
-- TOC entry 3592 (class 0 OID 0)
-- Dependencies: 228
-- Name: kontak_id_kontak_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.kontak_id_kontak_seq OWNED BY public.kontak.id_kontak;


--
-- TOC entry 244 (class 1259 OID 60947)
-- Name: konten; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.konten (
    id_konten integer NOT NULL,
    id_kategori integer,
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
-- TOC entry 243 (class 1259 OID 60946)
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
-- TOC entry 3593 (class 0 OID 0)
-- Dependencies: 243
-- Name: konten_id_konten_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.konten_id_konten_seq OWNED BY public.konten.id_konten;


--
-- TOC entry 221 (class 1259 OID 60751)
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
-- TOC entry 220 (class 1259 OID 60750)
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
-- TOC entry 3594 (class 0 OID 0)
-- Dependencies: 220
-- Name: misi_id_misi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.misi_id_misi_seq OWNED BY public.misi.id_misi;


--
-- TOC entry 225 (class 1259 OID 60785)
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
-- TOC entry 224 (class 1259 OID 60784)
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
-- TOC entry 3595 (class 0 OID 0)
-- Dependencies: 224
-- Name: navbar_id_nav_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.navbar_id_nav_seq OWNED BY public.navbar.id_nav;


--
-- TOC entry 239 (class 1259 OID 60902)
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
    doi character varying(255),
    tanggal_publikasi date,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT publikasi_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'pending'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.publikasi OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 60918)
-- Name: publikasi_anggota; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.publikasi_anggota (
    id_publikasi integer NOT NULL,
    id_anggota integer NOT NULL,
    urutan_penulis integer DEFAULT 1
);


ALTER TABLE public.publikasi_anggota OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 60901)
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
-- TOC entry 3596 (class 0 OID 0)
-- Dependencies: 238
-- Name: publikasi_id_publikasi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.publikasi_id_publikasi_seq OWNED BY public.publikasi.id_publikasi;


--
-- TOC entry 250 (class 1259 OID 61005)
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
-- TOC entry 249 (class 1259 OID 61004)
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
-- TOC entry 3597 (class 0 OID 0)
-- Dependencies: 249
-- Name: riwayat_pengajuan_id_riwayat_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.riwayat_pengajuan_id_riwayat_seq OWNED BY public.riwayat_pengajuan.id_riwayat;


--
-- TOC entry 223 (class 1259 OID 60768)
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
-- TOC entry 222 (class 1259 OID 60767)
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
-- TOC entry 3598 (class 0 OID 0)
-- Dependencies: 222
-- Name: sejarah_id_sejarah_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sejarah_id_sejarah_seq OWNED BY public.sejarah.id_sejarah;


--
-- TOC entry 231 (class 1259 OID 60832)
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
-- TOC entry 230 (class 1259 OID 60831)
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
-- TOC entry 3599 (class 0 OID 0)
-- Dependencies: 230
-- Name: slider_id_slider_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.slider_id_slider_seq OWNED BY public.slider.id_slider;


--
-- TOC entry 235 (class 1259 OID 60868)
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
-- TOC entry 234 (class 1259 OID 60867)
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
-- TOC entry 3600 (class 0 OID 0)
-- Dependencies: 234
-- Name: social_media_anggota_id_social_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.social_media_anggota_id_social_seq OWNED BY public.social_media_anggota.id_social;


--
-- TOC entry 237 (class 1259 OID 60881)
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
-- TOC entry 236 (class 1259 OID 60880)
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
-- TOC entry 3601 (class 0 OID 0)
-- Dependencies: 236
-- Name: struktur_lab_id_struktur_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.struktur_lab_id_struktur_seq OWNED BY public.struktur_lab.id_struktur;


--
-- TOC entry 217 (class 1259 OID 60715)
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
-- TOC entry 216 (class 1259 OID 60714)
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
-- TOC entry 3602 (class 0 OID 0)
-- Dependencies: 216
-- Name: tentang_kami_id_profil_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tentang_kami_id_profil_seq OWNED BY public.tentang_kami.id_profil;


--
-- TOC entry 215 (class 1259 OID 60699)
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
-- TOC entry 214 (class 1259 OID 60698)
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
-- TOC entry 3603 (class 0 OID 0)
-- Dependencies: 214
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- TOC entry 219 (class 1259 OID 60733)
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
-- TOC entry 218 (class 1259 OID 60732)
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
-- TOC entry 3604 (class 0 OID 0)
-- Dependencies: 218
-- Name: visi_id_visi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visi_id_visi_seq OWNED BY public.visi.id_visi;


--
-- TOC entry 3291 (class 2604 OID 60852)
-- Name: anggota_lab id_anggota; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab ALTER COLUMN id_anggota SET DEFAULT nextval('public.anggota_lab_id_anggota_seq'::regclass);


--
-- TOC entry 3308 (class 2604 OID 60974)
-- Name: fasilitas id_fasilitas; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fasilitas ALTER COLUMN id_fasilitas SET DEFAULT nextval('public.fasilitas_id_fasilitas_seq'::regclass);


--
-- TOC entry 3282 (class 2604 OID 60803)
-- Name: footer id_footer; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer ALTER COLUMN id_footer SET DEFAULT nextval('public.footer_id_footer_seq'::regclass);


--
-- TOC entry 3311 (class 2604 OID 60991)
-- Name: galeri id_galeri; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri ALTER COLUMN id_galeri SET DEFAULT nextval('public.galeri_id_galeri_seq'::regclass);


--
-- TOC entry 3303 (class 2604 OID 60938)
-- Name: kategori id_kategori; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategori ALTER COLUMN id_kategori SET DEFAULT nextval('public.kategori_id_kategori_seq'::regclass);


--
-- TOC entry 3285 (class 2604 OID 60818)
-- Name: kontak id_kontak; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kontak ALTER COLUMN id_kontak SET DEFAULT nextval('public.kontak_id_kontak_seq'::regclass);


--
-- TOC entry 3305 (class 2604 OID 60950)
-- Name: konten id_konten; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten ALTER COLUMN id_konten SET DEFAULT nextval('public.konten_id_konten_seq'::regclass);


--
-- TOC entry 3273 (class 2604 OID 60754)
-- Name: misi id_misi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.misi ALTER COLUMN id_misi SET DEFAULT nextval('public.misi_id_misi_seq'::regclass);


--
-- TOC entry 3279 (class 2604 OID 60788)
-- Name: navbar id_nav; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.navbar ALTER COLUMN id_nav SET DEFAULT nextval('public.navbar_id_nav_seq'::regclass);


--
-- TOC entry 3299 (class 2604 OID 60905)
-- Name: publikasi id_publikasi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi ALTER COLUMN id_publikasi SET DEFAULT nextval('public.publikasi_id_publikasi_seq'::regclass);


--
-- TOC entry 3314 (class 2604 OID 61008)
-- Name: riwayat_pengajuan id_riwayat; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan ALTER COLUMN id_riwayat SET DEFAULT nextval('public.riwayat_pengajuan_id_riwayat_seq'::regclass);


--
-- TOC entry 3276 (class 2604 OID 60771)
-- Name: sejarah id_sejarah; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sejarah ALTER COLUMN id_sejarah SET DEFAULT nextval('public.sejarah_id_sejarah_seq'::regclass);


--
-- TOC entry 3288 (class 2604 OID 60835)
-- Name: slider id_slider; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.slider ALTER COLUMN id_slider SET DEFAULT nextval('public.slider_id_slider_seq'::regclass);


--
-- TOC entry 3294 (class 2604 OID 60871)
-- Name: social_media_anggota id_social; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_media_anggota ALTER COLUMN id_social SET DEFAULT nextval('public.social_media_anggota_id_social_seq'::regclass);


--
-- TOC entry 3295 (class 2604 OID 60884)
-- Name: struktur_lab id_struktur; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab ALTER COLUMN id_struktur SET DEFAULT nextval('public.struktur_lab_id_struktur_seq'::regclass);


--
-- TOC entry 3265 (class 2604 OID 60718)
-- Name: tentang_kami id_profil; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tentang_kami ALTER COLUMN id_profil SET DEFAULT nextval('public.tentang_kami_id_profil_seq'::regclass);


--
-- TOC entry 3262 (class 2604 OID 60702)
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- TOC entry 3269 (class 2604 OID 60736)
-- Name: visi id_visi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visi ALTER COLUMN id_visi SET DEFAULT nextval('public.visi_id_visi_seq'::regclass);


--
-- TOC entry 3564 (class 0 OID 60849)
-- Dependencies: 233
-- Data for Name: anggota_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.anggota_lab (id_anggota, nama, foto, nip, email, kontak, biodata_teks, pendidikan, pendidikan_terakhir, bidang_keahlian, tanggal_bergabung, ruangan, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3577 (class 0 OID 60971)
-- Dependencies: 246
-- Data for Name: fasilitas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fasilitas (id_fasilitas, judul, gambar, deskripsi, kategori_fasilitas, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3558 (class 0 OID 60800)
-- Dependencies: 227
-- Data for Name: footer; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.footer (id_footer, logo, id_user, status, updated_at) FROM stdin;
\.


--
-- TOC entry 3579 (class 0 OID 60988)
-- Dependencies: 248
-- Data for Name: galeri; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.galeri (id_galeri, gambar, judul, deskripsi, filter_kategori, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3573 (class 0 OID 60935)
-- Dependencies: 242
-- Data for Name: kategori; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.kategori (id_kategori, nama, slug, created_at) FROM stdin;
1	Berita	berita	2025-12-01 17:49:36.405905
2	Pengumuman	pengumuman	2025-12-01 17:49:36.405905
3	Agenda	agenda	2025-12-01 17:49:36.405905
\.


--
-- TOC entry 3560 (class 0 OID 60815)
-- Dependencies: 229
-- Data for Name: kontak; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.kontak (id_kontak, whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, id_user, status, updated_at) FROM stdin;
\.


--
-- TOC entry 3575 (class 0 OID 60947)
-- Dependencies: 244
-- Data for Name: konten; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.konten (id_konten, id_kategori, judul, slug, isi, gambar, tanggal_posting, id_user, status) FROM stdin;
\.


--
-- TOC entry 3552 (class 0 OID 60751)
-- Dependencies: 221
-- Data for Name: misi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.misi (id_misi, isi_misi, urutan, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3556 (class 0 OID 60785)
-- Dependencies: 225
-- Data for Name: navbar; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.navbar (id_nav, logo, id_user, status, updated_at) FROM stdin;
\.


--
-- TOC entry 3570 (class 0 OID 60902)
-- Dependencies: 239
-- Data for Name: publikasi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.publikasi (id_publikasi, judul, cover, abstrak, tahun, jurnal, file_path, doi, tanggal_publikasi, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3571 (class 0 OID 60918)
-- Dependencies: 240
-- Data for Name: publikasi_anggota; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.publikasi_anggota (id_publikasi, id_anggota, urutan_penulis) FROM stdin;
\.


--
-- TOC entry 3581 (class 0 OID 61005)
-- Dependencies: 250
-- Data for Name: riwayat_pengajuan; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.riwayat_pengajuan (id_riwayat, tabel_sumber, id_data, id_operator, id_admin, status_lama, status_baru, catatan, created_at) FROM stdin;
\.


--
-- TOC entry 3554 (class 0 OID 60768)
-- Dependencies: 223
-- Data for Name: sejarah; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sejarah (id_sejarah, tahun, judul, deskripsi, urutan, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3562 (class 0 OID 60832)
-- Dependencies: 231
-- Data for Name: slider; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.slider (id_slider, gambar, judul, deskripsi, urutan, id_user, status, tanggal_dibuat) FROM stdin;
\.


--
-- TOC entry 3566 (class 0 OID 60868)
-- Dependencies: 235
-- Data for Name: social_media_anggota; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.social_media_anggota (id_social, id_anggota, platform, url) FROM stdin;
\.


--
-- TOC entry 3568 (class 0 OID 60881)
-- Dependencies: 237
-- Data for Name: struktur_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.struktur_lab (id_struktur, id_anggota, jabatan, urutan, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3548 (class 0 OID 60715)
-- Dependencies: 217
-- Data for Name: tentang_kami; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tentang_kami (id_profil, profil_lab, logo_lab, penjelasan_logo, id_user, status, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3546 (class 0 OID 60699)
-- Dependencies: 215
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id_user, username, password, nama, email, role, created_at) FROM stdin;
1	admin	0192023a7bbd73250516f069df18b500	Administrator	adminlabdt@gmail.com	admin	2025-12-01 17:49:36.405905
5	operator	1c3b2b820d4d63d61bb64abd0c4f76d0	Operator	operatorlabdt@gmail.com	operator	2025-12-01 19:09:16.648288
\.


--
-- TOC entry 3550 (class 0 OID 60733)
-- Dependencies: 219
-- Data for Name: visi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.visi (id_visi, isi_visi, urutan, id_user, status, created_at) FROM stdin;
\.


--
-- TOC entry 3605 (class 0 OID 0)
-- Dependencies: 232
-- Name: anggota_lab_id_anggota_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.anggota_lab_id_anggota_seq', 1, false);


--
-- TOC entry 3606 (class 0 OID 0)
-- Dependencies: 245
-- Name: fasilitas_id_fasilitas_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fasilitas_id_fasilitas_seq', 1, false);


--
-- TOC entry 3607 (class 0 OID 0)
-- Dependencies: 226
-- Name: footer_id_footer_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.footer_id_footer_seq', 1, false);


--
-- TOC entry 3608 (class 0 OID 0)
-- Dependencies: 247
-- Name: galeri_id_galeri_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.galeri_id_galeri_seq', 1, false);


--
-- TOC entry 3609 (class 0 OID 0)
-- Dependencies: 241
-- Name: kategori_id_kategori_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.kategori_id_kategori_seq', 3, true);


--
-- TOC entry 3610 (class 0 OID 0)
-- Dependencies: 228
-- Name: kontak_id_kontak_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.kontak_id_kontak_seq', 1, false);


--
-- TOC entry 3611 (class 0 OID 0)
-- Dependencies: 243
-- Name: konten_id_konten_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.konten_id_konten_seq', 1, false);


--
-- TOC entry 3612 (class 0 OID 0)
-- Dependencies: 220
-- Name: misi_id_misi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.misi_id_misi_seq', 1, false);


--
-- TOC entry 3613 (class 0 OID 0)
-- Dependencies: 224
-- Name: navbar_id_nav_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.navbar_id_nav_seq', 1, false);


--
-- TOC entry 3614 (class 0 OID 0)
-- Dependencies: 238
-- Name: publikasi_id_publikasi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.publikasi_id_publikasi_seq', 1, false);


--
-- TOC entry 3615 (class 0 OID 0)
-- Dependencies: 249
-- Name: riwayat_pengajuan_id_riwayat_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.riwayat_pengajuan_id_riwayat_seq', 1, false);


--
-- TOC entry 3616 (class 0 OID 0)
-- Dependencies: 222
-- Name: sejarah_id_sejarah_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sejarah_id_sejarah_seq', 1, false);


--
-- TOC entry 3617 (class 0 OID 0)
-- Dependencies: 230
-- Name: slider_id_slider_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.slider_id_slider_seq', 1, false);


--
-- TOC entry 3618 (class 0 OID 0)
-- Dependencies: 234
-- Name: social_media_anggota_id_social_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.social_media_anggota_id_social_seq', 1, false);


--
-- TOC entry 3619 (class 0 OID 0)
-- Dependencies: 236
-- Name: struktur_lab_id_struktur_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.struktur_lab_id_struktur_seq', 1, false);


--
-- TOC entry 3620 (class 0 OID 0)
-- Dependencies: 216
-- Name: tentang_kami_id_profil_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tentang_kami_id_profil_seq', 1, false);


--
-- TOC entry 3621 (class 0 OID 0)
-- Dependencies: 214
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_user_seq', 5, true);


--
-- TOC entry 3622 (class 0 OID 0)
-- Dependencies: 218
-- Name: visi_id_visi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visi_id_visi_seq', 1, false);


--
-- TOC entry 3355 (class 2606 OID 60861)
-- Name: anggota_lab anggota_lab_nip_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab
    ADD CONSTRAINT anggota_lab_nip_key UNIQUE (nip);


--
-- TOC entry 3357 (class 2606 OID 60859)
-- Name: anggota_lab anggota_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab
    ADD CONSTRAINT anggota_lab_pkey PRIMARY KEY (id_anggota);


--
-- TOC entry 3377 (class 2606 OID 60981)
-- Name: fasilitas fasilitas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fasilitas
    ADD CONSTRAINT fasilitas_pkey PRIMARY KEY (id_fasilitas);


--
-- TOC entry 3349 (class 2606 OID 60808)
-- Name: footer footer_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer
    ADD CONSTRAINT footer_pkey PRIMARY KEY (id_footer);


--
-- TOC entry 3379 (class 2606 OID 60998)
-- Name: galeri galeri_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri
    ADD CONSTRAINT galeri_pkey PRIMARY KEY (id_galeri);


--
-- TOC entry 3367 (class 2606 OID 60943)
-- Name: kategori kategori_nama_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategori
    ADD CONSTRAINT kategori_nama_key UNIQUE (nama);


--
-- TOC entry 3369 (class 2606 OID 60941)
-- Name: kategori kategori_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategori
    ADD CONSTRAINT kategori_pkey PRIMARY KEY (id_kategori);


--
-- TOC entry 3371 (class 2606 OID 60945)
-- Name: kategori kategori_slug_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategori
    ADD CONSTRAINT kategori_slug_key UNIQUE (slug);


--
-- TOC entry 3351 (class 2606 OID 60825)
-- Name: kontak kontak_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kontak
    ADD CONSTRAINT kontak_pkey PRIMARY KEY (id_kontak);


--
-- TOC entry 3373 (class 2606 OID 60957)
-- Name: konten konten_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten
    ADD CONSTRAINT konten_pkey PRIMARY KEY (id_konten);


--
-- TOC entry 3375 (class 2606 OID 60959)
-- Name: konten konten_slug_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten
    ADD CONSTRAINT konten_slug_key UNIQUE (slug);


--
-- TOC entry 3343 (class 2606 OID 60761)
-- Name: misi misi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.misi
    ADD CONSTRAINT misi_pkey PRIMARY KEY (id_misi);


--
-- TOC entry 3347 (class 2606 OID 60793)
-- Name: navbar navbar_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.navbar
    ADD CONSTRAINT navbar_pkey PRIMARY KEY (id_nav);


--
-- TOC entry 3365 (class 2606 OID 60923)
-- Name: publikasi_anggota publikasi_anggota_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi_anggota
    ADD CONSTRAINT publikasi_anggota_pkey PRIMARY KEY (id_publikasi, id_anggota);


--
-- TOC entry 3363 (class 2606 OID 60912)
-- Name: publikasi publikasi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi
    ADD CONSTRAINT publikasi_pkey PRIMARY KEY (id_publikasi);


--
-- TOC entry 3381 (class 2606 OID 61013)
-- Name: riwayat_pengajuan riwayat_pengajuan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan
    ADD CONSTRAINT riwayat_pengajuan_pkey PRIMARY KEY (id_riwayat);


--
-- TOC entry 3345 (class 2606 OID 60778)
-- Name: sejarah sejarah_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sejarah
    ADD CONSTRAINT sejarah_pkey PRIMARY KEY (id_sejarah);


--
-- TOC entry 3353 (class 2606 OID 60842)
-- Name: slider slider_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.slider
    ADD CONSTRAINT slider_pkey PRIMARY KEY (id_slider);


--
-- TOC entry 3359 (class 2606 OID 60874)
-- Name: social_media_anggota social_media_anggota_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_media_anggota
    ADD CONSTRAINT social_media_anggota_pkey PRIMARY KEY (id_social);


--
-- TOC entry 3361 (class 2606 OID 60890)
-- Name: struktur_lab struktur_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab
    ADD CONSTRAINT struktur_lab_pkey PRIMARY KEY (id_struktur);


--
-- TOC entry 3339 (class 2606 OID 60726)
-- Name: tentang_kami tentang_kami_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tentang_kami
    ADD CONSTRAINT tentang_kami_pkey PRIMARY KEY (id_profil);


--
-- TOC entry 3333 (class 2606 OID 60713)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 3335 (class 2606 OID 60709)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id_user);


--
-- TOC entry 3337 (class 2606 OID 60711)
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- TOC entry 3341 (class 2606 OID 60744)
-- Name: visi visi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visi
    ADD CONSTRAINT visi_pkey PRIMARY KEY (id_visi);


--
-- TOC entry 3390 (class 2606 OID 60862)
-- Name: anggota_lab anggota_lab_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anggota_lab
    ADD CONSTRAINT anggota_lab_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3399 (class 2606 OID 60982)
-- Name: fasilitas fasilitas_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fasilitas
    ADD CONSTRAINT fasilitas_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3387 (class 2606 OID 60809)
-- Name: footer footer_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer
    ADD CONSTRAINT footer_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3400 (class 2606 OID 60999)
-- Name: galeri galeri_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri
    ADD CONSTRAINT galeri_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3388 (class 2606 OID 60826)
-- Name: kontak kontak_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kontak
    ADD CONSTRAINT kontak_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3397 (class 2606 OID 60960)
-- Name: konten konten_id_kategori_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten
    ADD CONSTRAINT konten_id_kategori_fkey FOREIGN KEY (id_kategori) REFERENCES public.kategori(id_kategori) ON DELETE SET NULL;


--
-- TOC entry 3398 (class 2606 OID 60965)
-- Name: konten konten_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten
    ADD CONSTRAINT konten_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3384 (class 2606 OID 60762)
-- Name: misi misi_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.misi
    ADD CONSTRAINT misi_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3386 (class 2606 OID 60794)
-- Name: navbar navbar_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.navbar
    ADD CONSTRAINT navbar_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3395 (class 2606 OID 60929)
-- Name: publikasi_anggota publikasi_anggota_id_anggota_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi_anggota
    ADD CONSTRAINT publikasi_anggota_id_anggota_fkey FOREIGN KEY (id_anggota) REFERENCES public.anggota_lab(id_anggota) ON DELETE CASCADE;


--
-- TOC entry 3396 (class 2606 OID 60924)
-- Name: publikasi_anggota publikasi_anggota_id_publikasi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi_anggota
    ADD CONSTRAINT publikasi_anggota_id_publikasi_fkey FOREIGN KEY (id_publikasi) REFERENCES public.publikasi(id_publikasi) ON DELETE CASCADE;


--
-- TOC entry 3394 (class 2606 OID 60913)
-- Name: publikasi publikasi_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi
    ADD CONSTRAINT publikasi_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3401 (class 2606 OID 61019)
-- Name: riwayat_pengajuan riwayat_pengajuan_id_admin_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan
    ADD CONSTRAINT riwayat_pengajuan_id_admin_fkey FOREIGN KEY (id_admin) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3402 (class 2606 OID 61014)
-- Name: riwayat_pengajuan riwayat_pengajuan_id_operator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.riwayat_pengajuan
    ADD CONSTRAINT riwayat_pengajuan_id_operator_fkey FOREIGN KEY (id_operator) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3385 (class 2606 OID 60779)
-- Name: sejarah sejarah_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sejarah
    ADD CONSTRAINT sejarah_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3389 (class 2606 OID 60843)
-- Name: slider slider_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.slider
    ADD CONSTRAINT slider_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3391 (class 2606 OID 60875)
-- Name: social_media_anggota social_media_anggota_id_anggota_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_media_anggota
    ADD CONSTRAINT social_media_anggota_id_anggota_fkey FOREIGN KEY (id_anggota) REFERENCES public.anggota_lab(id_anggota) ON DELETE CASCADE;


--
-- TOC entry 3392 (class 2606 OID 60891)
-- Name: struktur_lab struktur_lab_id_anggota_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab
    ADD CONSTRAINT struktur_lab_id_anggota_fkey FOREIGN KEY (id_anggota) REFERENCES public.anggota_lab(id_anggota) ON DELETE CASCADE;


--
-- TOC entry 3393 (class 2606 OID 60896)
-- Name: struktur_lab struktur_lab_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.struktur_lab
    ADD CONSTRAINT struktur_lab_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3382 (class 2606 OID 60727)
-- Name: tentang_kami tentang_kami_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tentang_kami
    ADD CONSTRAINT tentang_kami_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 3383 (class 2606 OID 60745)
-- Name: visi visi_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visi
    ADD CONSTRAINT visi_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


-- Completed on 2025-12-14 14:36:35

--
-- PostgreSQL database dump complete
--

\unrestrict 599szSiHcMjmrKIE92jYYX17xqqkH2jyb2yf0uJVFF4cvytmheZpo13foxV4QF6

