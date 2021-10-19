--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.15
-- Dumped by pg_dump version 9.6.15

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'SQL_ASCII';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: fuzzystrmatch; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS fuzzystrmatch WITH SCHEMA public;


--
-- Name: EXTENSION fuzzystrmatch; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION fuzzystrmatch IS 'determine similarities and distance between strings';


--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- Name: flag; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.flag AS ENUM (
    'Y',
    'N'
);


ALTER TYPE public.flag OWNER TO postgres;

--
-- Name: trg_set_owner(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_set_owner() RETURNS event_trigger
    LANGUAGE plpgsql
    AS $$ DECLARE   obj record;   types varchar[] := ARRAY['TYPE','TABLE','SEQUENCE','INDEX','SCHEMA','FUNCTION','DOMAIN','VIEW'];   type varchar; BEGIN   FOREACH type IN ARRAY types LOOP    FOR obj IN SELECT * FROM pg_event_trigger_ddl_commands() WHERE command_tag like 'CREATE ' || type LOOP       EXECUTE format('ALTER %s %s OWNER TO "%s"', obj.object_type, obj.object_identity, 'postgres');     END LOOP;   END LOOP; END; $$;


ALTER FUNCTION public.trg_set_owner() OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: _migration; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public._migration (
    version bigint NOT NULL,
    migration_name character varying(100),
    start_time timestamp without time zone,
    end_time timestamp without time zone,
    breakpoint boolean DEFAULT false NOT NULL
);


ALTER TABLE public._migration OWNER TO postgres;

--
-- Name: contact; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contact (
    contact_id integer NOT NULL,
    company_unit_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    contact_type character varying(255) NOT NULL,
    "position" character varying(255) NOT NULL,
    department character varying(255) NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(255) NOT NULL,
    phone_job character varying(255) NOT NULL,
    comment character varying(255),
    contact_for_invoice character varying(255),
    contact_for_contract character varying(255),
    contact_for_service character varying(255),
    contact_for_support character varying(255),
    salutation character varying(255),
    contact_for_payroll_export character varying(255),
    user_id integer,
    contact_for_stored_data character varying(255),
    contact_for_company_unit_admin character varying(255),
    contact_for_employee_admin character varying(255),
);


ALTER TABLE public.contact OWNER TO postgres;

--
-- Name: company_contact_ContactID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."company_contact_ContactID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_contact_ContactID_seq" OWNER TO postgres;

--
-- Name: company_contact_ContactID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."company_contact_ContactID_seq" OWNED BY public.contact.contact_id;


--
-- Name: device; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.device (
    device_id character varying(255) NOT NULL,
    client character varying(10) NOT NULL,
    private_key character varying(255) NOT NULL,
    push_token character varying(255),
    user_id integer,
    created timestamp without time zone NOT NULL,
    owner_id integer
);


ALTER TABLE public.device OWNER TO postgres;

--
-- Name: device_version; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.device_version (
    version_id integer NOT NULL,
    device_id character varying(255) NOT NULL,
    version character varying(255) NOT NULL,
    user_id integer NOT NULL,
    created timestamp without time zone NOT NULL
);


ALTER TABLE public.device_version OWNER TO postgres;

--
-- Name: employee; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee (
    employee_id integer NOT NULL,
    user_id integer,
    company_unit_id integer NOT NULL,
    material_status character varying(255),
    child_count character varying(255),
    start_date date,
    working_days_per_week character varying(255),
    cost_center_number character varying(255),
    comment character varying(255),
    acc_meal_value_tax_flat character varying(255),
    acc_food_subsidy_tax_free character varying(255),
    acc_gross_salary character varying(255),
    acc_grant_of_materials character varying(255),
    acc_internet_subsidy_tax character varying(255),
    acc_mobile_subsidy_tax_free character varying(255),
    acc_recreation_subsidy_tax_flat character varying(255),
    acc_net_income character varying(255),
    employee_guid character varying(255),
    active_contract_number character varying(255),
    archive public.flag DEFAULT 'N'::public.flag NOT NULL,
    license_version integer DEFAULT 0 NOT NULL,
    uses_application public.flag DEFAULT 'N'::public.flag NOT NULL,
    guideline_version integer DEFAULT 0 NOT NULL,
    acc_bonus_tax_flat character varying(255),
    acc_transport_tax_free character varying(255),
    work_place character varying(50),
    org_guideline_version integer DEFAULT 0 NOT NULL,
    givve_access_token character varying(255),
    givve_refresh_token character varying(255),
    acc_child_care_tax_free character varying(255),
    last_api_call timestamp without time zone,
    acc_travel_tax_free character varying(255),
    lan_meal_allowance character varying(255),
    acc_daily_allowance character varying(255),
    acc_ticket character varying(255),
    acc_accommodation character varying(255),
    acc_hospitality character varying(255),
    acc_parking character varying(255),
    acc_other character varying(255),
    acc_travel_costs character varying(255),
    acc_creditor character varying(255),
    acc_gift character varying(255),
    acc_corporate_health_management character varying(255),
    bank_name character varying(255),
    iban text,
    bic character varying(255),
    creditor_number character varying(255),
    master_data_export_id integer,
    master_data_export_update_id integer,
    yearly_total_benefits character varying(255)
);


ALTER TABLE public.employee OWNER TO postgres;

--
-- Name: employee_EmployeeID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."employee_EmployeeID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."employee_EmployeeID_seq" OWNER TO postgres;

--
-- Name: employee_EmployeeID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."employee_EmployeeID_seq" OWNED BY public.employee.employee_id;


--
-- Name: employee_history_EmployeeID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."employee_history_EmployeeID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."employee_history_EmployeeID_seq" OWNER TO postgres;

--
-- Name: operation_OperationID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."operation_OperationID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."operation_OperationID_seq" OWNER TO postgres;

--
-- Name: partner_contact_ContactID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."partner_contact_ContactID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."partner_contact_ContactID_seq" OWNER TO postgres;

--
-- Name: partner_contact; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partner_contact (
    partner_contact_id integer DEFAULT nextval('public."partner_contact_ContactID_seq"'::regclass) NOT NULL,
    partner_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    contact_type character varying(255) NOT NULL,
    "position" character varying(255) NOT NULL,
    department character varying(255) NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(255) NOT NULL,
    phone_job character varying(255) NOT NULL,
    comment character varying(255),
    salutation character varying(255),
    user_id integer,
    contact_for_commission public.flag DEFAULT 'N'::public.flag,
    contact_for_service public.flag DEFAULT 'N'::public.flag,
    contact_for_support public.flag DEFAULT 'N'::public.flag,
    contact_for_contract public.flag DEFAULT 'N'::public.flag
);


ALTER TABLE public.partner_contact OWNER TO postgres;

--
-- Name: permission; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permission (
    permission_id integer NOT NULL,
    name character varying(255) NOT NULL,
    title character varying(255),
    link_to character varying(255)
);


ALTER TABLE public.permission OWNER TO postgres;

--
-- Name: user_session; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_session (
    session_id character(255) NOT NULL,
    in_cookie integer NOT NULL,
    expire_date timestamp without time zone,
    session_data text NOT NULL,
    user_id integer
);


ALTER TABLE public.user_session OWNER TO postgres;

--
-- Name: session_SessionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."session_SessionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."session_SessionID_seq" OWNER TO postgres;

--
-- Name: session_SessionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."session_SessionID_seq" OWNED BY public.user_session.session_id;


--
-- Name: user_info; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_info (
    user_id integer NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    first_name text NOT NULL,
    phone character varying(255),
    birthday date,
    created timestamp without time zone NOT NULL,
    last_login timestamp without time zone,
    last_ip character varying(255),
    user_image character varying(255),
    user_image_config text,
    last_name text NOT NULL,
    salutation character varying(255),
    zip_code character varying(255),
    country character varying(255),
    city character varying(255),
    street text,
    house character varying(255),
    archive public.flag DEFAULT 'N'::public.flag NOT NULL,
    belongs_to_company text,
    access_token character varying(255),
    access_token_expire_date timestamp without time zone
);


ALTER TABLE public.user_info OWNER TO postgres;

--
-- Name: user_UserID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."user_UserID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_UserID_seq" OWNER TO postgres;

--
-- Name: user_UserID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."user_UserID_seq" OWNED BY public.user_info.user_id;


--
-- Name: user_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_permissions (
    user_permission_id integer NOT NULL,
    user_id integer NOT NULL,
    permission_id integer NOT NULL,
    link_id integer
);


ALTER TABLE public.user_permissions OWNER TO postgres;

--
-- Name: user_permissions_UserPermissionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."user_permissions_UserPermissionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_permissions_UserPermissionID_seq" OWNER TO postgres;

--
-- Name: user_permissions_UserPermissionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."user_permissions_UserPermissionID_seq" OWNED BY public.user_permissions.user_permission_id;


--
-- Name: user_roles_RoleID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."user_roles_RoleID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_roles_RoleID_seq" OWNER TO postgres;

--
-- Name: user_roles_RoleID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."user_roles_RoleID_seq" OWNED BY public.permission.permission_id;


--
-- Name: contact contact_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contact ALTER COLUMN contact_id SET DEFAULT nextval('public."company_contact_ContactID_seq"'::regclass);


--
-- Name: employee employee_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee ALTER COLUMN employee_id SET DEFAULT nextval('public."employee_EmployeeID_seq"'::regclass);


--
-- Name: permission permission_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission ALTER COLUMN permission_id SET DEFAULT nextval('public."user_roles_RoleID_seq"'::regclass);


--
-- Name: user_info user_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_info ALTER COLUMN user_id SET DEFAULT nextval('public."user_UserID_seq"'::regclass);


--
-- Name: user_permissions user_permission_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_permissions ALTER COLUMN user_permission_id SET DEFAULT nextval('public."user_permissions_UserPermissionID_seq"'::regclass);


--
-- Name: _migration _migration_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public._migration
    ADD CONSTRAINT _migration_pkey PRIMARY KEY (version);


--
-- Name: contact company_contact_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contact
    ADD CONSTRAINT company_contact_pk PRIMARY KEY (contact_id);


--
-- Name: device device_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.device
    ADD CONSTRAINT device_pkey PRIMARY KEY (device_id);


--
-- Name: device_version device_version_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.device_version
    ADD CONSTRAINT device_version_pkey PRIMARY KEY (version_id);


--
-- Name: employee employee_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee
    ADD CONSTRAINT employee_pk PRIMARY KEY (employee_id);


--
-- Name: partner_contact partner_contact_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partner_contact
    ADD CONSTRAINT partner_contact_pk PRIMARY KEY (partner_contact_id);


--
-- Name: user_session session_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_session
    ADD CONSTRAINT session_pkey PRIMARY KEY (session_id);


--
-- Name: user_info user_Email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_info
    ADD CONSTRAINT "user_Email_key" UNIQUE (email);


--
-- Name: user_permissions user_permissions_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_permissions
    ADD CONSTRAINT user_permissions_pk PRIMARY KEY (user_permission_id);


--
-- Name: user_info user_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_info
    ADD CONSTRAINT user_pk PRIMARY KEY (user_id);


--
-- Name: permission user_roles_Name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission
    ADD CONSTRAINT "user_roles_Name_key" UNIQUE (name);


--
-- Name: permission user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (permission_id);


--
-- Name: employee_company_unit_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_company_unit_id_key ON public.employee USING btree (company_unit_id);


--
-- Name: employee_user_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_user_id_key ON public.employee USING btree (user_id);


--
-- Name: device device_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.device
    ADD CONSTRAINT device_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.user_info(user_id);


--
-- Name: user_permissions user_permissions_RoleID_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_permissions
    ADD CONSTRAINT "user_permissions_RoleID_fkey" FOREIGN KEY (permission_id) REFERENCES public.permission(permission_id);


--
-- Name: user_permissions user_permissions_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_permissions
    ADD CONSTRAINT user_permissions_fk0 FOREIGN KEY (user_id) REFERENCES public.user_info(user_id);


--
-- Name: trg_set_owner; Type: EVENT TRIGGER; Schema: -; Owner: postgres
--

CREATE EVENT TRIGGER trg_set_owner ON ddl_command_end
         WHEN TAG IN ('CREATE TYPE', 'CREATE TABLE', 'CREATE SEQUENCE', 'CREATE INDEX', 'CREATE SCHEMA', 'CREATE FUNCTION', 'CREATE DOMAIN', 'CREATE VIEW')
   EXECUTE PROCEDURE public.trg_set_owner();


ALTER EVENT TRIGGER trg_set_owner OWNER TO postgres;

--
-- PostgreSQL database dump complete
--