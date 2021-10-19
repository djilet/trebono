CREATE
USER docker;
CREATE
DATABASE docker;
GRANT ALL PRIVILEGES ON DATABASE
docker TO docker;

CREATE TABLE user_info
(
    user_id           integer                NOT NULL,
    email             character varying(255) NOT NULL,
    password          character varying(255) NOT NULL,
    name              character varying(255) NOT NULL,
    phone             character varying(255),
    address           character varying(255),
    birthday          timestamp without time zone,
    created           timestamp without time zone NOT NULL,
    last_login        timestamp without time zone,
    last_ip           character varying(255),
    user_image        character varying(255),
    user_image_config text
);
ALTER TABLE public.user_info OWNER TO postgres;

CREATE SEQUENCE "user_UserID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE CACHE 1;


ALTER TABLE public."user_UserID_seq" OWNER TO postgres;

ALTER SEQUENCE "user_UserID_seq" OWNED BY user_info.user_id;