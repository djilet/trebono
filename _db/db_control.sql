--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.14
-- Dumped by pg_dump version 9.6.14

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
-- Name: agreements_field_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.agreements_field_history (
    value_id integer NOT NULL,
    agreement_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value text NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer
);


ALTER TABLE public.agreements_field_history OWNER TO postgres;

--
-- Name: agreements_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.agreements_history (
    agreement_id integer NOT NULL,
    version integer NOT NULL,
    content text NOT NULL,
    user_id integer NOT NULL,
    created_at timestamp without time zone NOT NULL
);


ALTER TABLE public.agreements_history OWNER TO postgres;

--
-- Name: app_version_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.app_version_history (
    value_id integer NOT NULL,
    app_version_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.app_version_history OWNER TO postgres;

--
-- Name: company_contract_ContractID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."company_contract_ContractID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_contract_ContractID_seq" OWNER TO postgres;

--
-- Name: company_history_CompanyUnitID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."company_history_CompanyUnitID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_history_CompanyUnitID_seq" OWNER TO postgres;

--
-- Name: company_unit_contract; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.company_unit_contract (
    contract_id integer DEFAULT nextval('public."company_contract_ContractID_seq"'::regclass) NOT NULL,
    company_unit_id integer NOT NULL,
    product_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    start_date date NOT NULL,
    end_date date,
    start_user_id integer DEFAULT 0 NOT NULL,
    end_user_id integer,
    end_date_created timestamp without time zone,
    start_from text DEFAULT 'admin'::text NOT NULL,
    end_from text DEFAULT 'admin'::text NOT NULL
);


ALTER TABLE public.company_unit_contract OWNER TO postgres;

--
-- Name: company_unit_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.company_unit_history (
    value_id integer DEFAULT nextval('public."company_history_CompanyUnitID_seq"'::regclass) NOT NULL,
    company_unit_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL,
    created_from text DEFAULT 'admin'::text NOT NULL
);


ALTER TABLE public.company_unit_history OWNER TO postgres;

--
-- Name: config_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.config_history (
    id integer NOT NULL,
    user_id integer NOT NULL,
    config_id integer NOT NULL,
    value text NOT NULL,
    created timestamp without time zone NOT NULL
);


ALTER TABLE public.config_history OWNER TO postgres;

--
-- Name: config_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.config_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.config_history_id_seq OWNER TO postgres;

--
-- Name: config_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.config_history_id_seq OWNED BY public.config_history.id;


--
-- Name: user_contact_history_ValueID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."user_contact_history_ValueID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_contact_history_ValueID_seq" OWNER TO postgres;

--
-- Name: contact_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contact_history (
    value_id integer DEFAULT nextval('public."user_contact_history_ValueID_seq"'::regclass) NOT NULL,
    contact_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL,
    created_from text DEFAULT 'admin'::text NOT NULL
);


ALTER TABLE public.contact_history OWNER TO postgres;

--
-- Name: email_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.email_history (
    email_id integer NOT NULL,
    user_id integer NOT NULL,
    email text NOT NULL,
    is_sended public.flag NOT NULL,
    title text,
    file_name text,
    error_message text,
    created timestamp without time zone NOT NULL
);


ALTER TABLE public.email_history OWNER TO postgres;

--
-- Name: employee_contract_ContractID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."employee_contract_ContractID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."employee_contract_ContractID_seq" OWNER TO postgres;

--
-- Name: employee_contract; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee_contract (
    contract_id integer DEFAULT nextval('public."employee_contract_ContractID_seq"'::regclass) NOT NULL,
    employee_id integer NOT NULL,
    product_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    start_date date NOT NULL,
    end_date date,
    start_user_id integer DEFAULT 0 NOT NULL,
    end_user_id integer,
    end_date_created timestamp without time zone,
    start_from text DEFAULT 'admin'::text NOT NULL,
    end_from text DEFAULT 'admin'::text NOT NULL
);


ALTER TABLE public.employee_contract OWNER TO postgres;

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
-- Name: employee_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee_history (
    value_id integer DEFAULT nextval('public."employee_history_EmployeeID_seq"'::regclass) NOT NULL,
    employee_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL,
    created_from text DEFAULT 'admin'::text NOT NULL
);


ALTER TABLE public.employee_history OWNER TO postgres;

--
-- Name: import_company_unit_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.import_company_unit_history (
    import_id integer NOT NULL,
    user_id integer NOT NULL,
    company_unit_id integer,
    employee_count integer DEFAULT 0 NOT NULL,
    content text NOT NULL,
    ended public.flag NOT NULL,
    created timestamp without time zone NOT NULL,
    updated timestamp without time zone NOT NULL
);


ALTER TABLE public.import_company_unit_history OWNER TO postgres;

--
-- Name: ocr_request; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ocr_request (
    request_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    url character varying(255) NOT NULL,
    response_time integer NOT NULL,
    type character varying(255) NOT NULL,
    is_successful public.flag NOT NULL,
    is_receipt public.flag,
    receipt_id integer NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.ocr_request OWNER TO postgres;

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
-- Name: operation; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operation (
    operation_id integer DEFAULT nextval('public."operation_OperationID_seq"'::regclass) NOT NULL,
    date timestamp without time zone NOT NULL,
    user_id integer NOT NULL,
    ip character varying(255),
    link text NOT NULL,
    section character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    object_id integer
);


ALTER TABLE public.operation OWNER TO postgres;

--
-- Name: operation_cron; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operation_cron (
    operation_id integer NOT NULL,
    date timestamp without time zone NOT NULL,
    description text,
    is_successful public.flag NOT NULL,
    error_message text,
    status text,
    status_updated timestamp without time zone
);


ALTER TABLE public.operation_cron OWNER TO postgres;

--
-- Name: option_value_history_ValueID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."option_value_history_ValueID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."option_value_history_ValueID_seq" OWNER TO postgres;

--
-- Name: option_value_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.option_value_history (
    value_id integer DEFAULT nextval('public."option_value_history_ValueID_seq"'::regclass) NOT NULL,
    level character varying(255) NOT NULL,
    entity_id integer,
    option_id integer NOT NULL,
    value character varying(255),
    date_from timestamp without time zone NOT NULL,
    user_id integer NOT NULL,
    created_from text DEFAULT 'admin'::text NOT NULL,
    created timestamp without time zone
);


ALTER TABLE public.option_value_history OWNER TO postgres;

--
-- Name: partner_contact_history_ValueID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."partner_contact_history_ValueID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."partner_contact_history_ValueID_seq" OWNER TO postgres;

--
-- Name: partner_contact_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partner_contact_history (
    value_id integer DEFAULT nextval('public."partner_contact_history_ValueID_seq"'::regclass) NOT NULL,
    contact_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.partner_contact_history OWNER TO postgres;

--
-- Name: partner_contract_ContractID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."partner_contract_ContractID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."partner_contract_ContractID_seq" OWNER TO postgres;

--
-- Name: partner_contract; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partner_contract (
    partner_contract_id integer DEFAULT nextval('public."partner_contract_ContractID_seq"'::regclass) NOT NULL,
    partner_id integer,
    commission character varying(32) DEFAULT 0,
    long character varying(32) DEFAULT 0,
    implementation_fee character varying(32) DEFAULT 0,
    partner_type integer,
    start_date date,
    end_date date,
    company_unit_id integer NOT NULL,
    product_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    start_user_id integer,
    end_user_id integer
);


ALTER TABLE public.partner_contract OWNER TO postgres;

--
-- Name: partner_history_PartnerID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."partner_history_PartnerID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."partner_history_PartnerID_seq" OWNER TO postgres;

--
-- Name: partner_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partner_history (
    value_id integer DEFAULT nextval('public."partner_history_PartnerID_seq"'::regclass) NOT NULL,
    partner_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.partner_history OWNER TO postgres;

--
-- Name: push_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.push_history (
    push_id integer NOT NULL,
    user_id integer NOT NULL,
    device_id character varying(255) NOT NULL,
    is_sended public.flag NOT NULL,
    text text,
    error_message text,
    created timestamp without time zone NOT NULL
);


ALTER TABLE public.push_history OWNER TO postgres;

--
-- Name: receipt_file_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipt_file_log (
    receipt_file_id integer NOT NULL,
    updated timestamp without time zone NOT NULL,
    content text NOT NULL
);


ALTER TABLE public.receipt_file_log OWNER TO postgres;

--
-- Name: receipt_history_ReceiptID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."receipt_history_ReceiptID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_history_ReceiptID_seq" OWNER TO postgres;

--
-- Name: receipt_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipt_history (
    value_id integer DEFAULT nextval('public."receipt_history_ReceiptID_seq"'::regclass) NOT NULL,
    receipt_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.receipt_history OWNER TO postgres;

--
-- Name: user_history_UserID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."user_history_UserID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_history_UserID_seq" OWNER TO postgres;

--
-- Name: user_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_history (
    value_id integer DEFAULT nextval('public."user_history_UserID_seq"'::regclass) NOT NULL,
    end_user_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    start_user_id integer NOT NULL,
    created_from text DEFAULT 'admin'::text NOT NULL
);


ALTER TABLE public.user_history OWNER TO postgres;

--
-- Name: user_permission_history_ValueID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."user_permission_history_ValueID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_permission_history_ValueID_seq" OWNER TO postgres;

--
-- Name: user_permission_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_permission_history (
    value_id integer DEFAULT nextval('public."user_permission_history_ValueID_seq"'::regclass) NOT NULL,
    end_user_id integer NOT NULL,
    permission_id integer NOT NULL,
    value character varying(255),
    created timestamp without time zone NOT NULL,
    start_user_id integer NOT NULL,
    created_from text DEFAULT 'admin'::text NOT NULL
);


ALTER TABLE public.user_permission_history OWNER TO postgres;

--
-- Name: voucher_history_valueid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.voucher_history_valueid_seq
    START WITH 172
    INCREMENT BY 1
    MINVALUE 172
    NO MAXVALUE
    CACHE 172;

ALTER TABLE public.voucher_history_valueid_seq OWNER TO postgres;

--
-- Name: voucher_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.voucher_history (
    value_id integer DEFAULT nextval('public.voucher_history_valueid_seq'::regclass) NOT NULL,
    voucher_id integer NOT NULL,
    property_name character varying(255) NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer
);


ALTER TABLE public.voucher_history OWNER TO postgres;

--
-- Name: config_history id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.config_history ALTER COLUMN id SET DEFAULT nextval('public.config_history_id_seq'::regclass);


--
-- Name: _migration _migration_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public._migration
    ADD CONSTRAINT _migration_pkey PRIMARY KEY (version);


--
-- Name: agreements_field_history agreements_field_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.agreements_field_history
    ADD CONSTRAINT agreements_field_history_pkey PRIMARY KEY (value_id);


--
-- Name: agreements_history agreements_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.agreements_history
    ADD CONSTRAINT agreements_history_pkey PRIMARY KEY (agreement_id, version);


--
-- Name: app_version_history app_version_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_version_history
    ADD CONSTRAINT app_version_history_pkey PRIMARY KEY (value_id);


--
-- Name: company_unit_contract company_unit_contract_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit_contract
    ADD CONSTRAINT company_unit_contract_pkey PRIMARY KEY (contract_id);


--
-- Name: config_history config_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.config_history
    ADD CONSTRAINT config_history_pkey PRIMARY KEY (id);


--
-- Name: email_history email_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.email_history
    ADD CONSTRAINT email_history_pkey PRIMARY KEY (email_id);


--
-- Name: employee_contract employee_contract_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_contract
    ADD CONSTRAINT employee_contract_pkey PRIMARY KEY (contract_id);


--
-- Name: import_company_unit_history import_company_unit_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.import_company_unit_history
    ADD CONSTRAINT import_company_unit_history_pkey PRIMARY KEY (import_id);


--
-- Name: ocr_request ocr_request_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ocr_request
    ADD CONSTRAINT ocr_request_pkey PRIMARY KEY (request_id);


--
-- Name: operation_cron operation_cron_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_cron
    ADD CONSTRAINT operation_cron_pkey PRIMARY KEY (operation_id);


--
-- Name: push_history push_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.push_history
    ADD CONSTRAINT push_history_pkey PRIMARY KEY (push_id);


--
-- Name: receipt_file_log receipt_file_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_file_log
    ADD CONSTRAINT receipt_file_log_pkey PRIMARY KEY (receipt_file_id);


--
-- Name: voucher_history voucher_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.voucher_history
    ADD CONSTRAINT voucher_history_pkey PRIMARY KEY (value_id);


--
-- Name: company_unit_contract_company_unit_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX company_unit_contract_company_unit_id_key ON public.company_unit_contract USING btree (company_unit_id);


--
-- Name: company_unit_contract_end_date_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX company_unit_contract_end_date_key ON public.company_unit_contract USING btree (end_date);


--
-- Name: company_unit_contract_product_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX company_unit_contract_product_id_key ON public.company_unit_contract USING btree (product_id);


--
-- Name: company_unit_contract_start_date_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX company_unit_contract_start_date_key ON public.company_unit_contract USING btree (start_date);


--
-- Name: employee_contract_employee_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_contract_employee_id_key ON public.employee_contract USING btree (employee_id);


--
-- Name: employee_contract_end_date_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_contract_end_date_key ON public.employee_contract USING btree (end_date);


--
-- Name: employee_contract_product_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_contract_product_id_key ON public.employee_contract USING btree (product_id);


--
-- Name: employee_contract_start_date_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_contract_start_date_key ON public.employee_contract USING btree (start_date);


--
-- Name: option_value_created_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX option_value_date_from_key ON public.option_value_history USING btree (date_from);


--
-- Name: option_value_entity_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX option_value_entity_id_key ON public.option_value_history USING btree (entity_id);


--
-- Name: option_value_option_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX option_value_option_id_key ON public.option_value_history USING btree (option_id);


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