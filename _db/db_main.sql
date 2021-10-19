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
-- Name: option_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.option_type AS ENUM (
    'int',
    'float',
    'string',
    'currency',
    'flag'
);


ALTER TYPE public.option_type OWNER TO postgres;

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
-- Name: agreements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.agreements (
    agreement_id integer NOT NULL,
    group_id integer NOT NULL,
    organization_id integer NOT NULL,
    content text NOT NULL,
    version integer DEFAULT 1 NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    confirm_message character varying(500),
    new_only public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.agreements OWNER TO postgres;

--
-- Name: agreements_employee; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.agreements_employee (
    agreement_id integer NOT NULL,
    employee_id integer NOT NULL,
    device_id character varying(50) NOT NULL,
    version integer NOT NULL,
    device_info character varying(255) NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    file character varying(255)
);


ALTER TABLE public.agreements_employee OWNER TO postgres;

--
-- Name: app_version; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.app_version (
    app_version_id integer NOT NULL,
    app_version character varying(255) NOT NULL,
    client character varying(10) NOT NULL,
    critical public.flag DEFAULT 'N'::public.flag NOT NULL,
    created timestamp without time zone DEFAULT '2019-03-28 07:05:52'::timestamp without time zone NOT NULL,
    created_by integer DEFAULT '-2'::integer NOT NULL,
    archive public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.app_version OWNER TO postgres;

--
-- Name: app_version_app_version_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.app_version_app_version_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.app_version_app_version_id_seq OWNER TO postgres;

--
-- Name: app_version_app_version_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.app_version_app_version_id_seq OWNED BY public.app_version.app_version_id;


--
-- Name: commission_line_LineID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."commission_line_LineID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."commission_line_LineID_seq" OWNER TO postgres;

--
-- Name: commission_line; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.commission_line (
    commission_line_id integer DEFAULT nextval('public."commission_line_LineID_seq"'::regclass) NOT NULL,
    partner_id integer NOT NULL,
    company_unit_id integer NOT NULL,
    product_id integer NOT NULL,
    type character varying(4),
    value numeric(10,2),
    date date,
    revenue character varying DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.commission_line OWNER TO postgres;

--
-- Name: company; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.company (
    company_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    colorscheme character varying NOT NULL,
    company_image character varying(255),
    company_image_config text
);


ALTER TABLE public.company OWNER TO postgres;

--
-- Name: company_CompanyID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."company_CompanyID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_CompanyID_seq" OWNER TO postgres;

--
-- Name: company_CompanyID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."company_CompanyID_seq" OWNED BY public.company.company_id;


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
-- Name: company_unit; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.company_unit (
    company_unit_id integer NOT NULL,
    company_id integer,
    title text NOT NULL,
    parent_unit_id integer,
    created timestamp without time zone NOT NULL,
    phone character varying(255),
    email character varying(255),
    zip_code character varying(255),
    city character varying(255),
    street text,
    house character varying(255),
    client_id character varying(255),
    vat_payer_id character varying(255),
    country character varying(255),
    comment character varying(255),
    bank_details character varying(255),
    iban text,
    bic character varying(255),
    register character varying(255),
    tax_number character varying(255),
    payment_type character varying(255),
    invoice_date character varying(255),
    financial_statement_date character varying(255),
    acc_meal_value_tax_flat character varying(255),
    acc_food_subsidy_tax_free character varying(255),
    acc_gross_salary character varying(255),
    acc_grant_of_materials character varying(255),
    acc_internet_subsidy_tax character varying(255),
    acc_mobile_subsidy_tax_free character varying(255),
    acc_recreation_subsidy_tax_flat character varying(255),
    acc_net_income character varying(255),
    customer_guid character varying(255),
    tax_consultant character varying(255),
    payment_method character varying(255),
    app_logo_image character varying(70),
    app_logo_mini_image character varying(70),
    archive public.flag DEFAULT 'N'::public.flag NOT NULL,
    agreement_enable public.flag DEFAULT 'N'::public.flag NOT NULL,
    voucher_logo_image character varying(70),
    acc_bonus_tax_flat character varying(255),
    datev_format character varying(255) DEFAULT 'lodas'::character varying NOT NULL,
    acc_transport_tax_free character varying(255),
    acc_child_care_tax_free character varying(255),
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
    creditor_number text,
    sepa_service character varying(255),
    sepa_voucher character varying(255),
    sepa_service_date timestamp without time zone,
    sepa_voucher_date timestamp without time zone,
    master_data_service_id integer,
    master_data_voucher_id integer,
    master_data_sepa_service_id integer,
    master_data_sepa_voucher_id integer,
    master_data_service_update_id integer,
    master_data_voucher_update_id integer,
    master_data_sepa_service_update_id integer,
    master_data_sepa_voucher_update_id integer,
    payroll_month character varying(255) DEFAULT 'last_month'::character varying NOT NULL,
    datev_encoding character varying(255) DEFAULT 'utf-8'::character varying NOT NULL
);


ALTER TABLE public.company_unit OWNER TO postgres;

--
-- Name: company_unit_CompanyUnitID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."company_unit_CompanyUnitID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_unit_CompanyUnitID_seq" OWNER TO postgres;

--
-- Name: company_unit_CompanyUnitID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."company_unit_CompanyUnitID_seq" OWNED BY public.company_unit.company_unit_id;


--
-- Name: company_unit_option_value; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.company_unit_option_value (
    value_id integer NOT NULL,
    company_unit_id integer NOT NULL,
    option_id integer NOT NULL,
    value character varying(255),
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.company_unit_option_value OWNER TO postgres;

--
-- Name: company_unit_option_OptionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."company_unit_option_OptionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_unit_option_OptionID_seq" OWNER TO postgres;

--
-- Name: company_unit_option_OptionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."company_unit_option_OptionID_seq" OWNED BY public.company_unit_option_value.value_id;


--
-- Name: config_ConfigID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."config_ConfigID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."config_ConfigID_seq" OWNER TO postgres;

--
-- Name: config; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.config (
    config_id integer DEFAULT nextval('public."config_ConfigID_seq"'::regclass) NOT NULL,
    code character varying(255) NOT NULL,
    value text NOT NULL,
    group_code character varying(50) DEFAULT 'receipt_shop'::character varying NOT NULL,
    editor character varying(50) DEFAULT 'plain'::character varying NOT NULL,
    updated timestamp without time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
    sort_order integer DEFAULT 0
);


ALTER TABLE public.config OWNER TO postgres;

--
-- Name: option_value; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.option_value (
    value_id integer NOT NULL,
    option_id integer NOT NULL,
    value character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.option_value OWNER TO postgres;

--
-- Name: contract_option_OptionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."contract_option_OptionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."contract_option_OptionID_seq" OWNER TO postgres;

--
-- Name: contract_option_OptionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."contract_option_OptionID_seq" OWNED BY public.option_value.value_id;


--
-- Name: currency; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.currency (
    currency_id integer NOT NULL,
    title text NOT NULL,
    digit character varying(3) NOT NULL
);


ALTER TABLE public.currency OWNER TO postgres;

--
-- Name: currency_currency_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.currency_currency_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.currency_currency_id_seq OWNER TO postgres;

--
-- Name: currency_currency_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.currency_currency_id_seq OWNED BY public.currency.currency_id;


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
-- Name: employee_option_value; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee_option_value (
    value_id integer NOT NULL,
    employee_id integer NOT NULL,
    option_id integer NOT NULL,
    value character varying(255),
    created timestamp without time zone NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.employee_option_value OWNER TO postgres;

--
-- Name: employee_option_OptionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."employee_option_OptionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."employee_option_OptionID_seq" OWNER TO postgres;

--
-- Name: employee_option_OptionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."employee_option_OptionID_seq" OWNED BY public.employee_option_value.value_id;


--
-- Name: invoice; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invoice (
    invoice_id integer NOT NULL,
    company_unit_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    date_from date NOT NULL,
    date_to date NOT NULL,
    status character varying(255) NOT NULL,
    invoice_guid character varying(255),
    export_id integer,
    archive public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.invoice OWNER TO postgres;

--
-- Name: invoice_InvoiceID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."invoice_InvoiceID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."invoice_InvoiceID_seq" OWNER TO postgres;

--
-- Name: invoice_InvoiceID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."invoice_InvoiceID_seq" OWNED BY public.invoice.invoice_id;


--
-- Name: invoice_export_datev_ExportID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."invoice_export_datev_ExportID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."invoice_export_datev_ExportID_seq" OWNER TO postgres;

--
-- Name: invoice_export_datev; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invoice_export_datev (
    export_id integer DEFAULT nextval('public."invoice_export_datev_ExportID_seq"'::regclass) NOT NULL,
    user_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    export_number integer NOT NULL
);


ALTER TABLE public.invoice_export_datev OWNER TO postgres;

--
-- Name: invoice_line_InvoiceLineID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."invoice_line_InvoiceLineID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."invoice_line_InvoiceLineID_seq" OWNER TO postgres;

--
-- Name: invoice_line; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invoice_line (
    invoice_line_id integer DEFAULT nextval('public."invoice_line_InvoiceLineID_seq"'::regclass) NOT NULL,
    invoice_id integer NOT NULL,
    product_id integer NOT NULL,
    type character varying(255) NOT NULL,
    cost numeric(10,2) NOT NULL,
    quantity integer NOT NULL,
    company_unit_id integer
);


ALTER TABLE public.invoice_line OWNER TO postgres;

--
-- Name: language_variable; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.language_variable (
    variable_id integer NOT NULL,
    tag_name character varying(100) NOT NULL,
    value text,
    type character varying(8) NOT NULL,
    module character varying(20) NOT NULL,
    template character varying(60) NOT NULL,
    language_code character varying(2) NOT NULL
);


ALTER TABLE public.language_variable OWNER TO postgres;

--
-- Name: language_variable_variable_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.language_variable_variable_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.language_variable_variable_id_seq OWNER TO postgres;

--
-- Name: language_variable_variable_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.language_variable_variable_id_seq OWNED BY public.language_variable.variable_id;


--
-- Name: message; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.message (
    "MessageID" integer NOT NULL,
    "UserIDFrom" integer NOT NULL,
    "UserIDTo" integer NOT NULL,
    "AnswerTo" integer NOT NULL,
    "Test" text NOT NULL,
    "AttachedFile" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.message OWNER TO postgres;

--
-- Name: message_MessageID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."message_MessageID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."message_MessageID_seq" OWNER TO postgres;

--
-- Name: message_MessageID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."message_MessageID_seq" OWNED BY public.message."MessageID";


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
-- Name: option; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.option (
    option_id integer NOT NULL,
    type public.option_type NOT NULL,
    code character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    sort_order integer NOT NULL,
    product_id integer NOT NULL,
    group_id integer NOT NULL,
    level_global public.flag NOT NULL,
    level_company_unit public.flag NOT NULL,
    level_employee public.flag NOT NULL
);


ALTER TABLE public.option OWNER TO postgres;

--
-- Name: option_group_GroupID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."option_group_GroupID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."option_group_GroupID_seq" OWNER TO postgres;

--
-- Name: option_group; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.option_group (
    group_id integer DEFAULT nextval('public."option_group_GroupID_seq"'::regclass) NOT NULL,
    code character varying(255),
    title character varying(255),
    sort_order integer
);


ALTER TABLE public.option_group OWNER TO postgres;

--
-- Name: option_key_KeyID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."option_key_KeyID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."option_key_KeyID_seq" OWNER TO postgres;

--
-- Name: option_key_KeyID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."option_key_KeyID_seq" OWNED BY public.option.option_id;


--
-- Name: partner; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partner (
    "PartnerID" integer NOT NULL,
    company_unit_id integer,
    title character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    phone character varying(255),
    email character varying(255),
    zip_code character varying(255),
    city character varying(255),
    street character varying(255),
    house character varying(255),
    country character varying(255),
    comment character varying(255),
    bank_details character varying(255),
    iban text,
    bic character varying(255),
    register character varying(255),
    tax_number character varying(255),
    tax_consultant character varying(255),
    archive public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.partner OWNER TO postgres;

--
-- Name: partner_PartnerID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."partner_PartnerID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."partner_PartnerID_seq" OWNER TO postgres;

--
-- Name: partner_PartnerID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."partner_PartnerID_seq" OWNED BY public.partner."PartnerID";


--
-- Name: partner_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partner_type (
    partner_type_id integer NOT NULL,
    title character varying NOT NULL,
    abbreviation character varying NOT NULL,
    commission character varying(20),
    implementation_fee character varying(20),
    long character varying,
    period character varying,
    report_date character varying
);


ALTER TABLE public.partner_type OWNER TO postgres;

--
-- Name: payroll_payrollid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payroll_payrollid_seq
    START WITH 114
    INCREMENT BY 1
    MINVALUE 114
    NO MAXVALUE
    CACHE 114;


ALTER TABLE public.payroll_payrollid_seq OWNER TO postgres;

--
-- Name: payroll; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payroll (
    payroll_id integer DEFAULT nextval('public.payroll_payrollid_seq'::regclass) NOT NULL,
    company_unit_id integer NOT NULL,
    payroll_month character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    pdf_file character varying(255),
    status character varying,
    lodas_file character varying(255),
    lug_file character varying(255),
    logga_file character varying(255),
    topas_file character varying(255)
);


ALTER TABLE public.payroll OWNER TO postgres;

--
-- Name: product; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product (
    product_id integer NOT NULL,
    group_id integer NOT NULL,
    title character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    code character varying(255) NOT NULL,
    base_for_api public.flag DEFAULT 'N'::public.flag NOT NULL,
    inheritable public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.product OWNER TO postgres;

--
-- Name: product_ProductID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."product_ProductID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."product_ProductID_seq" OWNER TO postgres;

--
-- Name: product_ProductID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."product_ProductID_seq" OWNED BY public.product.product_id;


--
-- Name: product_group; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_group (
    group_id integer NOT NULL,
    title character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    code character varying(255) NOT NULL,
    sort_order integer NOT NULL,
    receipts character varying(255),
    need_check_image public.flag DEFAULT 'Y'::public.flag NOT NULL,
    product_group_image character varying(255),
    product_group_image_config text,
    multiple_receipt_file public.flag DEFAULT 'N'::public.flag NOT NULL,
    voucher public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.product_group OWNER TO postgres;

--
-- Name: product_group_2_receipt_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_group_2_receipt_type (
    product_group_receipt_type_id integer NOT NULL,
    group_id integer NOT NULL,
    code character varying(255) NOT NULL
);


ALTER TABLE public.product_group_2_receipt_type OWNER TO postgres;

--
-- Name: product_group_2_receipt_type_product_group_receipt_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.product_group_2_receipt_type_product_group_receipt_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.product_group_2_receipt_type_product_group_receipt_type_id_seq OWNER TO postgres;

--
-- Name: product_group_2_receipt_type_product_group_receipt_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.product_group_2_receipt_type_product_group_receipt_type_id_seq OWNED BY public.product_group_2_receipt_type.product_group_receipt_type_id;


--
-- Name: product_group_GroupID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."product_group_GroupID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."product_group_GroupID_seq" OWNER TO postgres;

--
-- Name: product_group_GroupID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."product_group_GroupID_seq" OWNED BY public.product_group.group_id;


--
-- Name: receipt; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipt (
    receipt_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    document_guid character varying(255),
    employee_id integer NOT NULL,
    status character varying NOT NULL,
    status_user_id integer NOT NULL,
    status_updated timestamp without time zone NOT NULL,
    updated timestamp without time zone NOT NULL,
    amount_approved numeric(10,2),
    document_date timestamp without time zone,
    store_name character varying(255),
    receipt_from character varying(255),
    group_id integer NOT NULL,
    archive public.flag DEFAULT 'N'::public.flag NOT NULL,
    real_amount_approved numeric(10,2),
    document_date_from date,
    document_date_to date,
    legal_receipt_id integer,
    version_id integer,
    datev_export character varying(255) DEFAULT '0'::character varying NOT NULL,
    pdf_export character varying(255) DEFAULT '0'::character varying NOT NULL,
    bookkeeping_export_pdf character varying(255) DEFAULT '0'::character varying NOT NULL,
    denial_reason text,
    automatic_processed public.flag DEFAULT 'N'::public.flag NOT NULL,
    comment text,
    trip_id integer,
    booked public.flag,
    ref_number character varying(255),
    acc_system character varying(255),
    currency_id integer,
    vat integer,
    days_amount_under_16 integer,
    days_amount_over_16 integer,
    sets_of_goods text,
    creditor_export_id character varying(255)
);


ALTER TABLE public.receipt OWNER TO postgres;

--
-- Name: receipt_ReceiptID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."receipt_ReceiptID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_ReceiptID_seq" OWNER TO postgres;

--
-- Name: receipt_ReceiptID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."receipt_ReceiptID_seq" OWNED BY public.receipt.receipt_id;


--
-- Name: receipt_comment; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipt_comment (
    comment_id integer NOT NULL,
    receipt_id integer NOT NULL,
    user_id integer NOT NULL,
    content text NOT NULL,
    comment_file character varying(255),
    created timestamp without time zone NOT NULL,
    archive public.flag DEFAULT 'N'::public.flag NOT NULL,
    read_by_admin public.flag DEFAULT 'N'::public.flag NOT NULL,
    read_by_employee public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.receipt_comment OWNER TO postgres;

--
-- Name: receipt_comment_CommentID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."receipt_comment_CommentID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_comment_CommentID_seq" OWNER TO postgres;

--
-- Name: receipt_comment_CommentID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."receipt_comment_CommentID_seq" OWNED BY public.receipt_comment.comment_id;


--
-- Name: receipt_file; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipt_file (
    receipt_file_id integer NOT NULL,
    receipt_id integer NOT NULL,
    file_image character varying(255) NOT NULL,
    created timestamp without time zone NOT NULL,
    hash character varying(255),
    signature_file character varying(255),
    signature_report_file character varying(255),
    signature_status character varying(50)
);


ALTER TABLE public.receipt_file OWNER TO postgres;

--
-- Name: receipt_files_ReceiptFileID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."receipt_files_ReceiptFileID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_files_ReceiptFileID_seq" OWNER TO postgres;

--
-- Name: receipt_files_ReceiptFileID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."receipt_files_ReceiptFileID_seq" OWNED BY public.receipt_file.receipt_file_id;


--
-- Name: receipt_line; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipt_line (
    line_id integer NOT NULL,
    receipt_id integer NOT NULL,
    line_number integer NOT NULL,
    title character varying(255) NOT NULL,
    quantity numeric(10,3) NOT NULL,
    price numeric(10,2) NOT NULL,
    created timestamp without time zone NOT NULL,
    sku character varying(255),
    vat character varying(255),
    approved character varying(255)
);


ALTER TABLE public.receipt_line OWNER TO postgres;

--
-- Name: receipt_line_LineID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."receipt_line_LineID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_line_LineID_seq" OWNER TO postgres;

--
-- Name: receipt_line_LineID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."receipt_line_LineID_seq" OWNED BY public.receipt_line.line_id;


--
-- Name: receipt_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipt_type (
    receipt_type_id integer NOT NULL,
    code character varying(255) NOT NULL,
    receipt_type_image character varying(255),
    receipt_type_image_config text,
    created timestamp without time zone NOT NULL,
    created_by integer NOT NULL,
    archive public.flag DEFAULT 'N'::public.flag NOT NULL
);


ALTER TABLE public.receipt_type OWNER TO postgres;

--
-- Name: receipt_type_receipt_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.receipt_type_receipt_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.receipt_type_receipt_type_id_seq OWNER TO postgres;

--
-- Name: receipt_type_receipt_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.receipt_type_receipt_type_id_seq OWNED BY public.receipt_type.receipt_type_id;


--
-- Name: report; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.report (
    "ReportID" integer NOT NULL,
    "UserID" integer NOT NULL,
    "Type" character varying NOT NULL,
    "Parameters" bytea NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.report OWNER TO postgres;

--
-- Name: report_ReportID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."report_ReportID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."report_ReportID_seq" OWNER TO postgres;

--
-- Name: report_ReportID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."report_ReportID_seq" OWNED BY public.report."ReportID";


--
-- Name: shop; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.shop (
    "ShopID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "Address" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.shop OWNER TO postgres;

--
-- Name: shop_ShopID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public."shop_ShopID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."shop_ShopID_seq" OWNER TO postgres;

--
-- Name: shop_ShopID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public."shop_ShopID_seq" OWNED BY public.shop."ShopID";


--
-- Name: trip; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.trip (
    trip_id integer NOT NULL,
    employee_id integer NOT NULL,
    created timestamp without time zone NOT NULL,
    finished_by_employee public.flag DEFAULT 'N'::public.flag NOT NULL,
    trip_name character varying(255),
    purpose character varying(255),
    start_date timestamp(6) without time zone,
    end_date timestamp(6) without time zone
);


ALTER TABLE public.trip OWNER TO postgres;

--
-- Name: trip_trip_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.trip_trip_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.trip_trip_id_seq OWNER TO postgres;

--
-- Name: trip_trip_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.trip_trip_id_seq OWNED BY public.trip.trip_id;


--
-- Name: voucher_voucherid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.voucher_voucherid_seq
    START WITH 70
    INCREMENT BY 1
    MINVALUE 70
    NO MAXVALUE
    CACHE 70;


ALTER TABLE public.voucher_voucherid_seq OWNER TO postgres;

--
-- Name: voucher; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.voucher (
    voucher_id integer DEFAULT nextval('public.voucher_voucherid_seq'::regclass) NOT NULL,
    employee_id integer NOT NULL,
    amount real NOT NULL,
    created timestamp without time zone NOT NULL,
    created_user_id integer NOT NULL,
    voucher_date date NOT NULL,
    reason character varying(255) NOT NULL,
    recurring public.flag NOT NULL,
    archive public.flag DEFAULT 'N'::public.flag NOT NULL,
    file character varying(255),
    end_date date NOT NULL,
    recurring_frequency character varying(10),
    recurring_end_date date,
    group_id integer,
    receipt_ids character varying(255)
);

--
-- Name: voucher_receipt; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.voucher_receipt (
    voucher_receipt_id integer DEFAULT nextval('public.voucher_receipt_voucher_receipt_id_seq'::regclass) NOT NULL,
    voucher_id integer NOT NULL,
    receipt_id integer NOT NULL,
    created timestamp without time zone NOT NULL
);


ALTER TABLE public.voucher OWNER TO postgres;

--
-- Name: app_version app_version_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_version ALTER COLUMN app_version_id SET DEFAULT nextval('public.app_version_app_version_id_seq'::regclass);


--
-- Name: company company_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company ALTER COLUMN company_id SET DEFAULT nextval('public."company_CompanyID_seq"'::regclass);


--
-- Name: company_unit company_unit_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit ALTER COLUMN company_unit_id SET DEFAULT nextval('public."company_unit_CompanyUnitID_seq"'::regclass);


--
-- Name: company_unit_option_value value_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit_option_value ALTER COLUMN value_id SET DEFAULT nextval('public."company_unit_option_OptionID_seq"'::regclass);


--
-- Name: currency currency_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.currency ALTER COLUMN currency_id SET DEFAULT nextval('public.currency_currency_id_seq'::regclass);


--
-- Name: employee_option_value value_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_option_value ALTER COLUMN value_id SET DEFAULT nextval('public."employee_option_OptionID_seq"'::regclass);


--
-- Name: invoice invoice_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice ALTER COLUMN invoice_id SET DEFAULT nextval('public."invoice_InvoiceID_seq"'::regclass);


--
-- Name: language_variable variable_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.language_variable ALTER COLUMN variable_id SET DEFAULT nextval('public.language_variable_variable_id_seq'::regclass);


--
-- Name: message MessageID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.message ALTER COLUMN "MessageID" SET DEFAULT nextval('public."message_MessageID_seq"'::regclass);


--
-- Name: option option_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option ALTER COLUMN option_id SET DEFAULT nextval('public."option_key_KeyID_seq"'::regclass);


--
-- Name: option_value value_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option_value ALTER COLUMN value_id SET DEFAULT nextval('public."contract_option_OptionID_seq"'::regclass);


--
-- Name: partner PartnerID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partner ALTER COLUMN "PartnerID" SET DEFAULT nextval('public."partner_PartnerID_seq"'::regclass);


--
-- Name: product product_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product ALTER COLUMN product_id SET DEFAULT nextval('public."product_ProductID_seq"'::regclass);


--
-- Name: product_group group_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_group ALTER COLUMN group_id SET DEFAULT nextval('public."product_group_GroupID_seq"'::regclass);


--
-- Name: product_group_2_receipt_type product_group_receipt_type_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_group_2_receipt_type ALTER COLUMN product_group_receipt_type_id SET DEFAULT nextval('public.product_group_2_receipt_type_product_group_receipt_type_id_seq'::regclass);


--
-- Name: receipt receipt_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt ALTER COLUMN receipt_id SET DEFAULT nextval('public."receipt_ReceiptID_seq"'::regclass);


--
-- Name: receipt_comment comment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_comment ALTER COLUMN comment_id SET DEFAULT nextval('public."receipt_comment_CommentID_seq"'::regclass);


--
-- Name: receipt_file receipt_file_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_file ALTER COLUMN receipt_file_id SET DEFAULT nextval('public."receipt_files_ReceiptFileID_seq"'::regclass);


--
-- Name: receipt_line line_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_line ALTER COLUMN line_id SET DEFAULT nextval('public."receipt_line_LineID_seq"'::regclass);


--
-- Name: receipt_type receipt_type_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_type ALTER COLUMN receipt_type_id SET DEFAULT nextval('public.receipt_type_receipt_type_id_seq'::regclass);


--
-- Name: report ReportID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report ALTER COLUMN "ReportID" SET DEFAULT nextval('public."report_ReportID_seq"'::regclass);


--
-- Name: shop ShopID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.shop ALTER COLUMN "ShopID" SET DEFAULT nextval('public."shop_ShopID_seq"'::regclass);


--
-- Name: trip trip_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip ALTER COLUMN trip_id SET DEFAULT nextval('public.trip_trip_id_seq'::regclass);


--
-- Name: _migration _migration_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public._migration
    ADD CONSTRAINT _migration_pkey PRIMARY KEY (version);


--
-- Name: agreements_employee agreements_employee_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.agreements_employee
    ADD CONSTRAINT agreements_employee_pkey PRIMARY KEY (agreement_id, employee_id, version);


--
-- Name: agreements agreements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.agreements
    ADD CONSTRAINT agreements_pkey PRIMARY KEY (agreement_id);


--
-- Name: app_version app_version_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_version
    ADD CONSTRAINT app_version_pkey PRIMARY KEY (app_version_id);


--
-- Name: receipt_comment comment_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_comment
    ADD CONSTRAINT comment_pk PRIMARY KEY (comment_id);


--
-- Name: commission_line commission_line_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.commission_line
    ADD CONSTRAINT commission_line_pkey PRIMARY KEY (commission_line_id);


--
-- Name: company company_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company
    ADD CONSTRAINT company_pk PRIMARY KEY (company_id);


--
-- Name: company_unit_option_value company_unit_option_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit_option_value
    ADD CONSTRAINT company_unit_option_pk PRIMARY KEY (value_id);


--
-- Name: company_unit company_unit_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit
    ADD CONSTRAINT company_unit_pk PRIMARY KEY (company_unit_id);


--
-- Name: config config_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.config
    ADD CONSTRAINT config_pkey PRIMARY KEY (config_id);


--
-- Name: option_value contract_option_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option_value
    ADD CONSTRAINT contract_option_pk PRIMARY KEY (value_id);


--
-- Name: currency currency_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.currency
    ADD CONSTRAINT currency_pkey PRIMARY KEY (currency_id);


--
-- Name: employee_option_value employee_option_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_option_value
    ADD CONSTRAINT employee_option_pk PRIMARY KEY (value_id);


--
-- Name: invoice_export_datev invoice_export_datev_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice_export_datev
    ADD CONSTRAINT invoice_export_datev_pkey PRIMARY KEY (export_id);


--
-- Name: invoice_line invoice_line_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice_line
    ADD CONSTRAINT invoice_line_pkey PRIMARY KEY (invoice_line_id);


--
-- Name: invoice invoice_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invoice
    ADD CONSTRAINT invoice_pk PRIMARY KEY (invoice_id);


--
-- Name: language_variable language_variable_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.language_variable
    ADD CONSTRAINT language_variable_pkey PRIMARY KEY (variable_id);


--
-- Name: message message_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.message
    ADD CONSTRAINT message_pk PRIMARY KEY ("MessageID");


--
-- Name: option_group option_group_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option_group
    ADD CONSTRAINT option_group_code_key UNIQUE (code);


--
-- Name: option_group option_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option_group
    ADD CONSTRAINT option_group_pkey PRIMARY KEY (group_id);


--
-- Name: option option_key_Name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option
    ADD CONSTRAINT "option_key_Name_key" UNIQUE (code);


--
-- Name: option option_key_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option
    ADD CONSTRAINT option_key_pk PRIMARY KEY (option_id);


--
-- Name: partner partner_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partner
    ADD CONSTRAINT partner_pk PRIMARY KEY ("PartnerID");


--
-- Name: partner_type partner_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partner_type
    ADD CONSTRAINT partner_type_pkey PRIMARY KEY (partner_type_id);


--
-- Name: payroll payroll_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payroll
    ADD CONSTRAINT payroll_pkey PRIMARY KEY (payroll_id);


--
-- Name: product_group_2_receipt_type product_group_2_receipt_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_group_2_receipt_type
    ADD CONSTRAINT product_group_2_receipt_type_pkey PRIMARY KEY (product_group_receipt_type_id);


--
-- Name: product_group product_group_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_group
    ADD CONSTRAINT product_group_pk PRIMARY KEY (group_id);


--
-- Name: product product_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product
    ADD CONSTRAINT product_pk PRIMARY KEY (product_id);


--
-- Name: receipt_file receipt_files_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_file
    ADD CONSTRAINT receipt_files_pk PRIMARY KEY (receipt_file_id);


--
-- Name: receipt_line receipt_line_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_line
    ADD CONSTRAINT receipt_line_pk PRIMARY KEY (line_id);


--
-- Name: receipt receipt_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt
    ADD CONSTRAINT receipt_pk PRIMARY KEY (receipt_id);


--
-- Name: receipt_type receipt_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_type
    ADD CONSTRAINT receipt_type_pkey PRIMARY KEY (receipt_type_id);


--
-- Name: report report_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report
    ADD CONSTRAINT report_pk PRIMARY KEY ("ReportID");


--
-- Name: shop shop_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.shop
    ADD CONSTRAINT shop_pk PRIMARY KEY ("ShopID");


--
-- Name: trip trip_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.trip
    ADD CONSTRAINT trip_pkey PRIMARY KEY (trip_id);


--
-- Name: voucher voucher_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.voucher
    ADD CONSTRAINT voucher_pkey PRIMARY KEY (voucher_id);


--
-- Name: agreements_group_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX agreements_group_id ON public.agreements USING btree (group_id);


--
-- Name: agreements_organization_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX agreements_organization_id ON public.agreements USING btree (organization_id);


--
-- Name: company_unit_customer_guid_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX company_unit_customer_guid_key ON public.company_unit USING btree (customer_guid);


--
-- Name: company_unit_parent_unit_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX company_unit_parent_unit_id_key ON public.company_unit USING btree (parent_unit_id);


--
-- Name: config_code_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX config_code_key ON public.config USING btree (code);


--
-- Name: language_variable_tag_name_type_module_template_language_code; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX language_variable_tag_name_type_module_template_language_code ON public.language_variable USING btree (tag_name, type, module, template, language_code);


--
-- Name: option_key_group_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX option_key_group_id_key ON public.option USING btree (group_id);


--
-- Name: product_code_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX product_code_key ON public.product USING btree (code);


--
-- Name: product_group_code_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX product_group_code_key ON public.product_group USING btree (code);


--
-- Name: receipt_comment_created_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX receipt_comment_created_key ON public.receipt_comment USING btree (created);


--
-- Name: receipt_comment_receipt_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX receipt_comment_receipt_id_key ON public.receipt_comment USING btree (receipt_id);


--
-- Name: receipt_comment_user_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX receipt_comment_user_id_key ON public.receipt_comment USING btree (user_id);


--
-- Name: receipt_employee_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX receipt_employee_id ON public.receipt USING btree (employee_id);


--
-- Name: receipt_group_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX receipt_group_id ON public.receipt USING btree (group_id);


--
-- Name: receipt_legal_receipt_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX receipt_legal_receipt_id ON public.receipt USING btree (legal_receipt_id);


--
-- Name: receipt_line_receipt_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX receipt_line_receipt_id ON public.receipt_line USING btree (receipt_id);


--
-- Name: receipt_type_code; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX receipt_type_code ON public.receipt_type USING btree (code);


--
-- Name: receipt_comment comment_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_comment
    ADD CONSTRAINT comment_fk0 FOREIGN KEY (receipt_id) REFERENCES public.receipt(receipt_id);


--
-- Name: company_unit company_unit_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit
    ADD CONSTRAINT company_unit_fk0 FOREIGN KEY (company_id) REFERENCES public.company(company_id);


--
-- Name: company_unit company_unit_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit
    ADD CONSTRAINT company_unit_fk1 FOREIGN KEY (parent_unit_id) REFERENCES public.company_unit(company_unit_id);


--
-- Name: company_unit_option_value company_unit_option_value_company_unit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit_option_value
    ADD CONSTRAINT company_unit_option_value_company_unit_id_fkey FOREIGN KEY (company_unit_id) REFERENCES public.company_unit(company_unit_id) ON DELETE CASCADE;


--
-- Name: company_unit_option_value company_unit_option_value_option_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_unit_option_value
    ADD CONSTRAINT company_unit_option_value_option_id_fkey FOREIGN KEY (option_id) REFERENCES public.option(option_id) ON DELETE CASCADE;


--
-- Name: option_value contract_option_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.option_value
    ADD CONSTRAINT contract_option_fk1 FOREIGN KEY (option_id) REFERENCES public.option(option_id);


--
-- Name: employee_option_value employee_option_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_option_value
    ADD CONSTRAINT employee_option_fk1 FOREIGN KEY (option_id) REFERENCES public.option(option_id);


--
-- Name: message message_fk2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.message
    ADD CONSTRAINT message_fk2 FOREIGN KEY ("AnswerTo") REFERENCES public.message("MessageID");


--
-- Name: product product_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product
    ADD CONSTRAINT product_fk0 FOREIGN KEY (group_id) REFERENCES public.product_group(group_id);


--
-- Name: product_group_2_receipt_type product_group_2_receipt_type_code_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_group_2_receipt_type
    ADD CONSTRAINT product_group_2_receipt_type_code_fkey FOREIGN KEY (code) REFERENCES public.receipt_type(code);


--
-- Name: product_group_2_receipt_type product_group_2_receipt_type_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_group_2_receipt_type
    ADD CONSTRAINT product_group_2_receipt_type_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.product_group(group_id);


--
-- Name: receipt_file receipt_files_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_file
    ADD CONSTRAINT receipt_files_fk0 FOREIGN KEY (receipt_id) REFERENCES public.receipt(receipt_id);


--
-- Name: receipt_line receipt_line_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipt_line
    ADD CONSTRAINT receipt_line_fk0 FOREIGN KEY (receipt_id) REFERENCES public.receipt(receipt_id);


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