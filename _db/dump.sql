--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: comment; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE comment (
    "CommentID" integer NOT NULL,
    "ReceiptID" integer NOT NULL,
    "UserID" integer NOT NULL,
    "AnswerTo" integer NOT NULL,
    "Text" text NOT NULL,
    "AttachedFile" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.comment OWNER TO postgres;

--
-- Name: comment_CommentID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "comment_CommentID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."comment_CommentID_seq" OWNER TO postgres;

--
-- Name: comment_CommentID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "comment_CommentID_seq" OWNED BY comment."CommentID";


--
-- Name: company; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE company (
    "CompanyID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL,
    "Colorscheme" character varying NOT NULL,
    "Logo" character varying(255) NOT NULL,
    "Address" character varying(255) NOT NULL
);


ALTER TABLE public.company OWNER TO postgres;

--
-- Name: company_CompanyID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "company_CompanyID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_CompanyID_seq" OWNER TO postgres;

--
-- Name: company_CompanyID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "company_CompanyID_seq" OWNED BY company."CompanyID";


--
-- Name: company_contact; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE company_contact (
    "ContactID" integer NOT NULL,
    "CompanyID" integer NOT NULL,
    "Name" character varying(255) NOT NULL,
    "Position" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.company_contact OWNER TO postgres;

--
-- Name: company_contact_ContactID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "company_contact_ContactID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_contact_ContactID_seq" OWNER TO postgres;

--
-- Name: company_contact_ContactID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "company_contact_ContactID_seq" OWNED BY company_contact."ContactID";


--
-- Name: company_unit; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE company_unit (
    "CompanyUnitID" integer NOT NULL,
    "CompanyID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "ParentUnitID" integer NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.company_unit OWNER TO postgres;

--
-- Name: company_unit_CompanyUnitID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "company_unit_CompanyUnitID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_unit_CompanyUnitID_seq" OWNER TO postgres;

--
-- Name: company_unit_CompanyUnitID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "company_unit_CompanyUnitID_seq" OWNED BY company_unit."CompanyUnitID";


--
-- Name: company_unit_option; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE company_unit_option (
    "OptionID" integer NOT NULL,
    "CompanyUnitID" integer NOT NULL,
    "KeyID" integer NOT NULL,
    "Value" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.company_unit_option OWNER TO postgres;

--
-- Name: company_unit_option_OptionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "company_unit_option_OptionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."company_unit_option_OptionID_seq" OWNER TO postgres;

--
-- Name: company_unit_option_OptionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "company_unit_option_OptionID_seq" OWNED BY company_unit_option."OptionID";


--
-- Name: contract; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE contract (
    "ContractID" integer NOT NULL,
    "CompanyID" integer NOT NULL,
    "ProductID" integer NOT NULL,
    "PartnerID" integer NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.contract OWNER TO postgres;

--
-- Name: contract_ContractID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "contract_ContractID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."contract_ContractID_seq" OWNER TO postgres;

--
-- Name: contract_ContractID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "contract_ContractID_seq" OWNED BY contract."ContractID";


--
-- Name: contract_option; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE contract_option (
    "OptionID" integer NOT NULL,
    "ContractID" integer NOT NULL,
    "KeyID" integer NOT NULL,
    "Value" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.contract_option OWNER TO postgres;

--
-- Name: contract_option_OptionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "contract_option_OptionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."contract_option_OptionID_seq" OWNER TO postgres;

--
-- Name: contract_option_OptionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "contract_option_OptionID_seq" OWNED BY contract_option."OptionID";


--
-- Name: device; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE device (
    device_id character varying(255) NOT NULL,
    client character varying(10) NOT NULL,
    private_key character varying(255) NOT NULL,
    push_token character varying(255),
    user_id integer,
    created timestamp without time zone NOT NULL
);


ALTER TABLE public.device OWNER TO postgres;

--
-- Name: employee; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE employee (
    "EmployeeID" integer NOT NULL,
    "UserID" integer NOT NULL,
    "CompanyUnitID" integer NOT NULL,
    "Role" character varying(255) NOT NULL,
    "StartDate" timestamp without time zone NOT NULL,
    "EndDate" timestamp without time zone NOT NULL
);


ALTER TABLE public.employee OWNER TO postgres;

--
-- Name: employee_EmployeeID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "employee_EmployeeID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."employee_EmployeeID_seq" OWNER TO postgres;

--
-- Name: employee_EmployeeID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "employee_EmployeeID_seq" OWNED BY employee."EmployeeID";


--
-- Name: employee_option; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE employee_option (
    "OptionID" integer NOT NULL,
    "EmployeeID" integer NOT NULL,
    "KeyID" integer NOT NULL,
    "Value" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.employee_option OWNER TO postgres;

--
-- Name: employee_option_OptionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "employee_option_OptionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."employee_option_OptionID_seq" OWNER TO postgres;

--
-- Name: employee_option_OptionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "employee_option_OptionID_seq" OWNED BY employee_option."OptionID";


--
-- Name: invoice; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE invoice (
    "InvoiceID" integer NOT NULL,
    "ContractID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "PeriodFrom" timestamp without time zone NOT NULL,
    "PeriodTo" timestamp without time zone NOT NULL,
    "Created" timestamp without time zone NOT NULL,
    "Status" character varying NOT NULL,
    "PayDate" timestamp without time zone NOT NULL
);


ALTER TABLE public.invoice OWNER TO postgres;

--
-- Name: invoice_InvoiceID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "invoice_InvoiceID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."invoice_InvoiceID_seq" OWNER TO postgres;

--
-- Name: invoice_InvoiceID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "invoice_InvoiceID_seq" OWNED BY invoice."InvoiceID";


--
-- Name: message; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE message (
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

CREATE SEQUENCE "message_MessageID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."message_MessageID_seq" OWNER TO postgres;

--
-- Name: message_MessageID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "message_MessageID_seq" OWNED BY message."MessageID";


--
-- Name: option_key; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE option_key (
    "KeyID" integer NOT NULL,
    "Type" character varying(255) NOT NULL,
    "Name" character varying(255) NOT NULL,
    "Title" character varying(255) NOT NULL
);


ALTER TABLE public.option_key OWNER TO postgres;

--
-- Name: option_key_KeyID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "option_key_KeyID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."option_key_KeyID_seq" OWNER TO postgres;

--
-- Name: option_key_KeyID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "option_key_KeyID_seq" OWNED BY option_key."KeyID";


--
-- Name: partner; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE partner (
    "PartnerID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "Address" character varying(255) NOT NULL,
    "Phone" character varying(255) NOT NULL,
    "Email" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.partner OWNER TO postgres;

--
-- Name: partner_PartnerID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "partner_PartnerID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."partner_PartnerID_seq" OWNER TO postgres;

--
-- Name: partner_PartnerID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "partner_PartnerID_seq" OWNED BY partner."PartnerID";


--
-- Name: permission; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE permission (
    permission_id integer NOT NULL,
    name character varying(255) NOT NULL,
    title character varying(255),
    link_to character varying(255)
);


ALTER TABLE public.permission OWNER TO postgres;

--
-- Name: product; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE product (
    "ProductID" integer NOT NULL,
    "GroupID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.product OWNER TO postgres;

--
-- Name: product_ProductID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "product_ProductID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."product_ProductID_seq" OWNER TO postgres;

--
-- Name: product_ProductID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "product_ProductID_seq" OWNED BY product."ProductID";


--
-- Name: product_group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE product_group (
    "GroupID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.product_group OWNER TO postgres;

--
-- Name: product_group_GroupID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "product_group_GroupID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."product_group_GroupID_seq" OWNER TO postgres;

--
-- Name: product_group_GroupID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "product_group_GroupID_seq" OWNED BY product_group."GroupID";


--
-- Name: receipt; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE receipt (
    "ReceiptID" integer NOT NULL,
    "Created" timestamp without time zone NOT NULL,
    "DocumentNumber" character varying(255) NOT NULL,
    "DocumentDate" timestamp without time zone NOT NULL,
    "DocumentPrice" timestamp without time zone NOT NULL,
    "EmployeeID" integer NOT NULL,
    "Status" character varying NOT NULL,
    "StatusBy" integer NOT NULL,
    "StatusDate" timestamp without time zone NOT NULL,
    "ShopID" integer NOT NULL
);


ALTER TABLE public.receipt OWNER TO postgres;

--
-- Name: receipt_ReceiptID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "receipt_ReceiptID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_ReceiptID_seq" OWNER TO postgres;

--
-- Name: receipt_ReceiptID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "receipt_ReceiptID_seq" OWNED BY receipt."ReceiptID";


--
-- Name: receipt_files; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE receipt_files (
    "ReceiptFileID" integer NOT NULL,
    "ReceiptID" integer NOT NULL,
    "FileName" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.receipt_files OWNER TO postgres;

--
-- Name: receipt_files_ReceiptFileID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "receipt_files_ReceiptFileID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_files_ReceiptFileID_seq" OWNER TO postgres;

--
-- Name: receipt_files_ReceiptFileID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "receipt_files_ReceiptFileID_seq" OWNED BY receipt_files."ReceiptFileID";


--
-- Name: receipt_line; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE receipt_line (
    "LineID" integer NOT NULL,
    "ReceiptID" integer NOT NULL,
    "Number" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "Count" integer NOT NULL,
    "Price" numeric NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.receipt_line OWNER TO postgres;

--
-- Name: receipt_line_LineID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "receipt_line_LineID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."receipt_line_LineID_seq" OWNER TO postgres;

--
-- Name: receipt_line_LineID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "receipt_line_LineID_seq" OWNED BY receipt_line."LineID";


--
-- Name: report; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE report (
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

CREATE SEQUENCE "report_ReportID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."report_ReportID_seq" OWNER TO postgres;

--
-- Name: report_ReportID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "report_ReportID_seq" OWNED BY report."ReportID";


--
-- Name: user_session; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_session (
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

CREATE SEQUENCE "session_SessionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."session_SessionID_seq" OWNER TO postgres;

--
-- Name: session_SessionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "session_SessionID_seq" OWNED BY user_session.session_id;


--
-- Name: shop; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE shop (
    "ShopID" integer NOT NULL,
    "Title" character varying(255) NOT NULL,
    "Address" character varying(255) NOT NULL,
    "Created" timestamp without time zone NOT NULL
);


ALTER TABLE public.shop OWNER TO postgres;

--
-- Name: shop_ShopID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "shop_ShopID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."shop_ShopID_seq" OWNER TO postgres;

--
-- Name: shop_ShopID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "shop_ShopID_seq" OWNED BY shop."ShopID";


--
-- Name: user_info; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_info (
    user_id integer NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    phone character varying(255),
    address character varying(255),
    birthday timestamp without time zone,
    created timestamp without time zone NOT NULL,
    last_login timestamp without time zone,
    last_ip character varying(255),
    user_image character varying(255),
    user_image_config text
);


ALTER TABLE public.user_info OWNER TO postgres;

--
-- Name: user_UserID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "user_UserID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_UserID_seq" OWNER TO postgres;

--
-- Name: user_UserID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "user_UserID_seq" OWNED BY user_info.user_id;


--
-- Name: user_permissions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_permissions (
    user_permission_id integer NOT NULL,
    user_id integer NOT NULL,
    permission_id integer NOT NULL,
    link_id integer
);


ALTER TABLE public.user_permissions OWNER TO postgres;

--
-- Name: user_permissions_UserPermissionID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "user_permissions_UserPermissionID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_permissions_UserPermissionID_seq" OWNER TO postgres;

--
-- Name: user_permissions_UserPermissionID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "user_permissions_UserPermissionID_seq" OWNED BY user_permissions.user_permission_id;


--
-- Name: user_roles_RoleID_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "user_roles_RoleID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user_roles_RoleID_seq" OWNER TO postgres;

--
-- Name: user_roles_RoleID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "user_roles_RoleID_seq" OWNED BY permission.permission_id;


--
-- Name: CommentID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY comment ALTER COLUMN "CommentID" SET DEFAULT nextval('"comment_CommentID_seq"'::regclass);


--
-- Name: CompanyID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company ALTER COLUMN "CompanyID" SET DEFAULT nextval('"company_CompanyID_seq"'::regclass);


--
-- Name: ContactID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_contact ALTER COLUMN "ContactID" SET DEFAULT nextval('"company_contact_ContactID_seq"'::regclass);


--
-- Name: CompanyUnitID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_unit ALTER COLUMN "CompanyUnitID" SET DEFAULT nextval('"company_unit_CompanyUnitID_seq"'::regclass);


--
-- Name: OptionID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_unit_option ALTER COLUMN "OptionID" SET DEFAULT nextval('"company_unit_option_OptionID_seq"'::regclass);


--
-- Name: ContractID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contract ALTER COLUMN "ContractID" SET DEFAULT nextval('"contract_ContractID_seq"'::regclass);


--
-- Name: OptionID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contract_option ALTER COLUMN "OptionID" SET DEFAULT nextval('"contract_option_OptionID_seq"'::regclass);


--
-- Name: EmployeeID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY employee ALTER COLUMN "EmployeeID" SET DEFAULT nextval('"employee_EmployeeID_seq"'::regclass);


--
-- Name: OptionID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY employee_option ALTER COLUMN "OptionID" SET DEFAULT nextval('"employee_option_OptionID_seq"'::regclass);


--
-- Name: InvoiceID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY invoice ALTER COLUMN "InvoiceID" SET DEFAULT nextval('"invoice_InvoiceID_seq"'::regclass);


--
-- Name: MessageID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY message ALTER COLUMN "MessageID" SET DEFAULT nextval('"message_MessageID_seq"'::regclass);


--
-- Name: KeyID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY option_key ALTER COLUMN "KeyID" SET DEFAULT nextval('"option_key_KeyID_seq"'::regclass);


--
-- Name: PartnerID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY partner ALTER COLUMN "PartnerID" SET DEFAULT nextval('"partner_PartnerID_seq"'::regclass);


--
-- Name: permission_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY permission ALTER COLUMN permission_id SET DEFAULT nextval('"user_roles_RoleID_seq"'::regclass);


--
-- Name: ProductID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY product ALTER COLUMN "ProductID" SET DEFAULT nextval('"product_ProductID_seq"'::regclass);


--
-- Name: GroupID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY product_group ALTER COLUMN "GroupID" SET DEFAULT nextval('"product_group_GroupID_seq"'::regclass);


--
-- Name: ReceiptID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt ALTER COLUMN "ReceiptID" SET DEFAULT nextval('"receipt_ReceiptID_seq"'::regclass);


--
-- Name: ReceiptFileID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt_files ALTER COLUMN "ReceiptFileID" SET DEFAULT nextval('"receipt_files_ReceiptFileID_seq"'::regclass);


--
-- Name: LineID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt_line ALTER COLUMN "LineID" SET DEFAULT nextval('"receipt_line_LineID_seq"'::regclass);


--
-- Name: ReportID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY report ALTER COLUMN "ReportID" SET DEFAULT nextval('"report_ReportID_seq"'::regclass);


--
-- Name: ShopID; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY shop ALTER COLUMN "ShopID" SET DEFAULT nextval('"shop_ShopID_seq"'::regclass);


--
-- Name: user_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_info ALTER COLUMN user_id SET DEFAULT nextval('"user_UserID_seq"'::regclass);


--
-- Name: user_permission_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_permissions ALTER COLUMN user_permission_id SET DEFAULT nextval('"user_permissions_UserPermissionID_seq"'::regclass);


--
-- Data for Name: comment; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: comment_CommentID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"comment_CommentID_seq"', 1, false);


--
-- Data for Name: company; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: company_CompanyID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"company_CompanyID_seq"', 1, false);


--
-- Data for Name: company_contact; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: company_contact_ContactID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"company_contact_ContactID_seq"', 1, false);


--
-- Data for Name: company_unit; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: company_unit_CompanyUnitID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"company_unit_CompanyUnitID_seq"', 1, false);


--
-- Data for Name: company_unit_option; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: company_unit_option_OptionID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"company_unit_option_OptionID_seq"', 1, false);


--
-- Data for Name: contract; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: contract_ContractID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"contract_ContractID_seq"', 1, false);


--
-- Data for Name: contract_option; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: contract_option_OptionID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"contract_option_OptionID_seq"', 1, false);


--
-- Data for Name: device; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO device VALUES ('11111', 'android', '29c4c97014b03803d448513a14ac0ea6', NULL, NULL, '2018-04-12 05:23:15');


--
-- Data for Name: employee; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: employee_EmployeeID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"employee_EmployeeID_seq"', 1, false);


--
-- Data for Name: employee_option; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: employee_option_OptionID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"employee_option_OptionID_seq"', 1, false);


--
-- Data for Name: invoice; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: invoice_InvoiceID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"invoice_InvoiceID_seq"', 1, false);


--
-- Data for Name: message; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: message_MessageID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"message_MessageID_seq"', 1, false);


--
-- Data for Name: option_key; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: option_key_KeyID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"option_key_KeyID_seq"', 1, false);


--
-- Data for Name: partner; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: partner_PartnerID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"partner_PartnerID_seq"', 1, false);


--
-- Data for Name: permission; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO permission VALUES (1, 'root', 'Administrator', '');
INSERT INTO permission VALUES (2, 'company', 'Company administrator', 'company');
INSERT INTO permission VALUES (3, 'employee', 'Employee administrator', 'company');
INSERT INTO permission VALUES (4, 'api', 'Mobile user', '');
INSERT INTO permission VALUES (5, 'receipt', 'Receipt processing', 'company');
INSERT INTO permission VALUES (6, 'support', 'Support manager', '');


--
-- Data for Name: product; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: product_ProductID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"product_ProductID_seq"', 1, false);


--
-- Data for Name: product_group; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: product_group_GroupID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"product_group_GroupID_seq"', 1, false);


--
-- Data for Name: receipt; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: receipt_ReceiptID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"receipt_ReceiptID_seq"', 1, false);


--
-- Data for Name: receipt_files; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: receipt_files_ReceiptFileID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"receipt_files_ReceiptFileID_seq"', 1, false);


--
-- Data for Name: receipt_line; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: receipt_line_LineID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"receipt_line_LineID_seq"', 1, false);


--
-- Data for Name: report; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: report_ReportID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"report_ReportID_seq"', 1, false);


--
-- Name: session_SessionID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"session_SessionID_seq"', 1, false);


--
-- Data for Name: shop; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: shop_ShopID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"shop_ShopID_seq"', 1, false);


--
-- Name: user_UserID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"user_UserID_seq"', 22, true);


--
-- Data for Name: user_info; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO user_info VALUES (1, 'test@test.ru', '7488e331b8b64e5794da3fa4eb10ad5d', 'Administrator', '+79990000001', '103132 Russia, Moscow, Kremlin', '2018-03-28 00:00:00', '2018-03-28 00:00:00', '2018-04-04 11:13:19.505566', '127.0.0.1', '1zr68fuz2h.jpg', '{"Thumb":{"X1":"0","Y1":"0","X2":"0","Y2":"0"},"Width":0,"Height":0}');


--
-- Data for Name: user_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO user_permissions VALUES (8, 1, 1, NULL);


--
-- Name: user_permissions_UserPermissionID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"user_permissions_UserPermissionID_seq"', 25, true);


--
-- Name: user_roles_RoleID_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('"user_roles_RoleID_seq"', 6, true);


--
-- Data for Name: user_session; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO user_session VALUES ('088945e9aa1adf08726b143e8a687a25                                                                                                                                                                                                                               ', 0, '2018-04-04 16:15:09.455942', 'a:1:{s:12:"LoggedInUser";a:26:{s:6:"UserID";s:1:"1";s:5:"Email";s:12:"test@test.ru";s:4:"Name";s:13:"Administrator";s:5:"Phone";s:12:"+79990000001";s:7:"Created";s:19:"2018-03-28 00:00:00";s:9:"LastLogin";s:26:"2018-04-03 14:21:39.466749";s:6:"LastIP";s:9:"127.0.0.1";s:9:"UserImage";s:14:"1zr68fuz2h.jpg";s:15:"UserImageConfig";s:68:"{"Thumb":{"X1":"0","Y1":"0","X2":"0","Y2":"0"},"Width":0,"Height":0}";s:9:"RoleTitle";s:0:"";s:16:"UserImageThumbX1";s:1:"0";s:16:"UserImageThumbY1";s:1:"0";s:16:"UserImageThumbX2";s:1:"0";s:16:"UserImageThumbY2";s:1:"0";s:14:"UserImageWidth";i:0;s:15:"UserImageHeight";i:0;s:18:"UserImageAdminPath";s:45:"/lst/images/lst-user-100x100_8/1zr68fuz2h.jpg";s:19:"UserImageAdminWidth";i:100;s:20:"UserImageAdminHeight";i:100;s:18:"UserImageThumbPath";s:54:"/lst/images/lst-user-100x100_0_0_0_0_13/1zr68fuz2h.jpg";s:19:"UserImageThumbWidth";i:100;s:20:"UserImageThumbHeight";i:100;s:17:"UserImageFullPath";s:45:"/lst/images/lst-user-100x100_0/1zr68fuz2h.jpg";s:18:"UserImageFullWidth";i:100;s:19:"UserImageFullHeight";i:100;s:17:"CommonPermissions";a:1:{i:0;s:4:"root";}}}', 1);


--
-- Name: comment_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY comment
    ADD CONSTRAINT comment_pk PRIMARY KEY ("CommentID");


--
-- Name: company_contact_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY company_contact
    ADD CONSTRAINT company_contact_pk PRIMARY KEY ("ContactID");


--
-- Name: company_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY company
    ADD CONSTRAINT company_pk PRIMARY KEY ("CompanyID");


--
-- Name: company_unit_option_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY company_unit_option
    ADD CONSTRAINT company_unit_option_pk PRIMARY KEY ("OptionID");


--
-- Name: company_unit_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY company_unit
    ADD CONSTRAINT company_unit_pk PRIMARY KEY ("CompanyUnitID");


--
-- Name: contract_option_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY contract_option
    ADD CONSTRAINT contract_option_pk PRIMARY KEY ("OptionID");


--
-- Name: contract_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY contract
    ADD CONSTRAINT contract_pk PRIMARY KEY ("ContractID");


--
-- Name: device_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY device
    ADD CONSTRAINT device_pkey PRIMARY KEY (device_id);


--
-- Name: employee_option_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY employee_option
    ADD CONSTRAINT employee_option_pk PRIMARY KEY ("OptionID");


--
-- Name: employee_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY employee
    ADD CONSTRAINT employee_pk PRIMARY KEY ("EmployeeID");


--
-- Name: invoice_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY invoice
    ADD CONSTRAINT invoice_pk PRIMARY KEY ("InvoiceID");


--
-- Name: message_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY message
    ADD CONSTRAINT message_pk PRIMARY KEY ("MessageID");


--
-- Name: option_key_Name_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY option_key
    ADD CONSTRAINT "option_key_Name_key" UNIQUE ("Name");


--
-- Name: option_key_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY option_key
    ADD CONSTRAINT option_key_pk PRIMARY KEY ("KeyID");


--
-- Name: partner_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY partner
    ADD CONSTRAINT partner_pk PRIMARY KEY ("PartnerID");


--
-- Name: product_group_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY product_group
    ADD CONSTRAINT product_group_pk PRIMARY KEY ("GroupID");


--
-- Name: product_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY product
    ADD CONSTRAINT product_pk PRIMARY KEY ("ProductID");


--
-- Name: receipt_files_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY receipt_files
    ADD CONSTRAINT receipt_files_pk PRIMARY KEY ("ReceiptFileID");


--
-- Name: receipt_line_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY receipt_line
    ADD CONSTRAINT receipt_line_pk PRIMARY KEY ("LineID");


--
-- Name: receipt_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY receipt
    ADD CONSTRAINT receipt_pk PRIMARY KEY ("ReceiptID");


--
-- Name: report_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY report
    ADD CONSTRAINT report_pk PRIMARY KEY ("ReportID");


--
-- Name: session_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_session
    ADD CONSTRAINT session_pkey PRIMARY KEY (session_id);


--
-- Name: shop_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY shop
    ADD CONSTRAINT shop_pk PRIMARY KEY ("ShopID");


--
-- Name: user_Email_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_info
    ADD CONSTRAINT "user_Email_key" UNIQUE (email);


--
-- Name: user_permissions_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_permissions
    ADD CONSTRAINT user_permissions_pk PRIMARY KEY (user_permission_id);


--
-- Name: user_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_info
    ADD CONSTRAINT user_pk PRIMARY KEY (user_id);


--
-- Name: user_roles_Name_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY permission
    ADD CONSTRAINT "user_roles_Name_key" UNIQUE (name);


--
-- Name: user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY permission
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (permission_id);


--
-- Name: comment_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY comment
    ADD CONSTRAINT comment_fk0 FOREIGN KEY ("ReceiptID") REFERENCES receipt("ReceiptID");


--
-- Name: comment_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY comment
    ADD CONSTRAINT comment_fk1 FOREIGN KEY ("UserID") REFERENCES user_info(user_id);


--
-- Name: comment_fk2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY comment
    ADD CONSTRAINT comment_fk2 FOREIGN KEY ("AnswerTo") REFERENCES comment("CommentID");


--
-- Name: company_contact_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_contact
    ADD CONSTRAINT company_contact_fk0 FOREIGN KEY ("CompanyID") REFERENCES company("CompanyID");


--
-- Name: company_unit_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_unit
    ADD CONSTRAINT company_unit_fk0 FOREIGN KEY ("CompanyID") REFERENCES company("CompanyID");


--
-- Name: company_unit_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_unit
    ADD CONSTRAINT company_unit_fk1 FOREIGN KEY ("ParentUnitID") REFERENCES company_unit("CompanyUnitID");


--
-- Name: company_unit_option_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_unit_option
    ADD CONSTRAINT company_unit_option_fk0 FOREIGN KEY ("CompanyUnitID") REFERENCES company_unit("CompanyUnitID");


--
-- Name: company_unit_option_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY company_unit_option
    ADD CONSTRAINT company_unit_option_fk1 FOREIGN KEY ("KeyID") REFERENCES option_key("KeyID");


--
-- Name: contract_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contract
    ADD CONSTRAINT contract_fk0 FOREIGN KEY ("CompanyID") REFERENCES company("CompanyID");


--
-- Name: contract_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contract
    ADD CONSTRAINT contract_fk1 FOREIGN KEY ("ProductID") REFERENCES product("ProductID");


--
-- Name: contract_fk2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contract
    ADD CONSTRAINT contract_fk2 FOREIGN KEY ("PartnerID") REFERENCES partner("PartnerID");


--
-- Name: contract_option_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contract_option
    ADD CONSTRAINT contract_option_fk0 FOREIGN KEY ("ContractID") REFERENCES contract("ContractID");


--
-- Name: contract_option_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY contract_option
    ADD CONSTRAINT contract_option_fk1 FOREIGN KEY ("KeyID") REFERENCES option_key("KeyID");


--
-- Name: device_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY device
    ADD CONSTRAINT device_user_id_fkey FOREIGN KEY (user_id) REFERENCES user_info(user_id);


--
-- Name: employee_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY employee
    ADD CONSTRAINT employee_fk0 FOREIGN KEY ("UserID") REFERENCES user_info(user_id);


--
-- Name: employee_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY employee
    ADD CONSTRAINT employee_fk1 FOREIGN KEY ("CompanyUnitID") REFERENCES company_unit("CompanyUnitID");


--
-- Name: employee_option_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY employee_option
    ADD CONSTRAINT employee_option_fk0 FOREIGN KEY ("EmployeeID") REFERENCES employee("EmployeeID");


--
-- Name: employee_option_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY employee_option
    ADD CONSTRAINT employee_option_fk1 FOREIGN KEY ("KeyID") REFERENCES option_key("KeyID");


--
-- Name: invoice_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY invoice
    ADD CONSTRAINT invoice_fk0 FOREIGN KEY ("ContractID") REFERENCES contract("ContractID");


--
-- Name: message_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY message
    ADD CONSTRAINT message_fk0 FOREIGN KEY ("UserIDFrom") REFERENCES user_info(user_id);


--
-- Name: message_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY message
    ADD CONSTRAINT message_fk1 FOREIGN KEY ("UserIDTo") REFERENCES user_info(user_id);


--
-- Name: message_fk2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY message
    ADD CONSTRAINT message_fk2 FOREIGN KEY ("AnswerTo") REFERENCES message("MessageID");


--
-- Name: product_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY product
    ADD CONSTRAINT product_fk0 FOREIGN KEY ("GroupID") REFERENCES product_group("GroupID");


--
-- Name: receipt_files_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt_files
    ADD CONSTRAINT receipt_files_fk0 FOREIGN KEY ("ReceiptID") REFERENCES receipt("ReceiptID");


--
-- Name: receipt_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt
    ADD CONSTRAINT receipt_fk0 FOREIGN KEY ("EmployeeID") REFERENCES employee("EmployeeID");


--
-- Name: receipt_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt
    ADD CONSTRAINT receipt_fk1 FOREIGN KEY ("StatusBy") REFERENCES user_info(user_id);


--
-- Name: receipt_fk2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt
    ADD CONSTRAINT receipt_fk2 FOREIGN KEY ("ShopID") REFERENCES shop("ShopID");


--
-- Name: receipt_line_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY receipt_line
    ADD CONSTRAINT receipt_line_fk0 FOREIGN KEY ("ReceiptID") REFERENCES receipt("ReceiptID");


--
-- Name: report_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY report
    ADD CONSTRAINT report_fk0 FOREIGN KEY ("UserID") REFERENCES user_info(user_id);


--
-- Name: user_permissions_RoleID_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_permissions
    ADD CONSTRAINT "user_permissions_RoleID_fkey" FOREIGN KEY (permission_id) REFERENCES permission(permission_id);


--
-- Name: user_permissions_fk0; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_permissions
    ADD CONSTRAINT user_permissions_fk0 FOREIGN KEY (user_id) REFERENCES user_info(user_id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

