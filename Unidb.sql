--
-- PostgreSQL database dump
--

-- Dumped from database version 15.2
-- Dumped by pg_dump version 15.2

-- Started on 2023-06-23 16:20:33

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
-- TOC entry 6 (class 2615 OID 16893)
-- Name: Unidb; Type: SCHEMA; Schema: -; Owner: buondonno
--

CREATE SCHEMA "Unidb";


ALTER SCHEMA "Unidb" OWNER TO buondonno;

--
-- TOC entry 229 (class 1255 OID 17979)
-- Name: archiviazione_carriera(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.archiviazione_carriera() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
	insert into "Unidb".storico_carriera(matricola,id_corso,id_insegnamento,id_docente,data_esame,voto)
	values (old.matricola,old.id_corso,old.id_insegnamento,old.id_docente,old.data_esame,old.voto);

return old;
end
$$;


ALTER FUNCTION public.archiviazione_carriera() OWNER TO postgres;

--
-- TOC entry 245 (class 1255 OID 17977)
-- Name: archiviazione_studente(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.archiviazione_studente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	insert into "Unidb".storico_studente(matricola,id_corso,nome,cognome,anno_immatricolazione,nome_utente,"password")
	values(old.matricola,old.id_corso,old.nome,old.cognome,old.anno_immatricolazione,old.nome_utente,old.password);
	
	delete from "Unidb".carriera where matricola=old.matricola;
	RETURN old;
END
$$;


ALTER FUNCTION public.archiviazione_studente() OWNER TO postgres;

--
-- TOC entry 247 (class 1255 OID 17985)
-- Name: controllo_ins_carriera(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_ins_carriera() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
	corso_studente varchar(6);
	resp_insegnamento varchar(6);
	n_occorrenze_insegnamento_in_corso bigint;
	
BEGIN
	select id_corso
	from "Unidb".studente
	where matricola=new.matricola
	into corso_studente;

	if(new.id_corso<>corso_studente)
	then
		raise exception 'Lo studente non è iscritto al corso inserito';
	end if;
	
	select insegnamento.id_responsabile
	from "Unidb".insegnamento
	where insegnamento.id=new.id_insegnamento 
	into resp_insegnamento;
	
	IF (resp_insegnamento<>new.id_docente)
	THEN
		RAISE EXCEPTION 'Il docente inserito non è il responsabile per insegnamento inserito';
	END IF;
	
	select count(composizione.id_insegnamento)
	from "Unidb".composizione
	where id_corso=new.id_corso and id_insegnamento=new.id_insegnamento
	into n_occorrenze_insegnamento_in_corso;
	
	if(n_occorrenze_insegnamento_in_corso <>1)
	then
		raise exception 'Insegnamento non fa parte del corso inserito';
	end if;

	RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_ins_carriera() OWNER TO postgres;

--
-- TOC entry 244 (class 1255 OID 17939)
-- Name: controllo_ins_data_anno_insegnamento(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_ins_data_anno_insegnamento() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
		anno_new_ins smallint;
		n_esami_stesso_anno bigint;
BEGIN
	with anno_new_ins as(	
	select insegnamento.anno as anno
	from "Unidb".insegnamento insegnamento
	where insegnamento.id=new.id_insegnamento
),
	corsi_ins as(
		select composizione.id_corso
		from "Unidb".composizione
		where id_insegnamento=new.id_insegnamento
	),
	corsi_ins_data as(
		select distinct id_corso
		from "Unidb".composizione,"Unidb".esame,anno_new_ins,"Unidb".insegnamento
		where esame.data_esame=new.data_esame and insegnamento.id=esame.id_insegnamento and esame.id_insegnamento=composizione.id_insegnamento and insegnamento.anno=anno_new_ins.anno and composizione.id_insegnamento<>new.id_insegnamento
	)
	select count(insegnamento.nome) 
	from "Unidb".insegnamento,"Unidb".esame,corsi_ins_data,corsi_ins,"Unidb".composizione
	where corsi_ins_data.id_corso=corsi_ins.id_corso and esame.data_esame=new.data_esame
	and insegnamento.id=esame.id_insegnamento and esame.id_insegnamento<>new.id_insegnamento
	and composizione.id_corso=corsi_ins_data.id_corso and composizione.id_insegnamento=esame.id_insegnamento
	into n_esami_stesso_anno;
		
	IF (n_esami_stesso_anno>0)
	THEN
		RAISE EXCEPTION 'Non ci possono essere esami per insegnamenti dello stesso anno nella stessa data';
	END IF;
	
	RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_ins_data_anno_insegnamento() OWNER TO postgres;

--
-- TOC entry 228 (class 1255 OID 17946)
-- Name: controllo_ins_esame_resp_corretto(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_ins_esame_resp_corretto() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	resp_insegnamento varchar(6);	
BEGIN
	select insegnamento.id_responsabile
	from "Unidb".insegnamento
	where insegnamento.id=new.id_insegnamento 
	into resp_insegnamento;
	
	IF (new.id_docente<>resp_insegnamento)
	THEN
		RAISE EXCEPTION 'Il responsabile inserito non corrisponde con insegnamento inserito';
	END IF;
	RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_ins_esame_resp_corretto() OWNER TO postgres;

--
-- TOC entry 227 (class 1255 OID 17944)
-- Name: controllo_ins_insegnamento_max_3_resp_per_docente(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_ins_insegnamento_max_3_resp_per_docente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	n_ins_docente bigint;
BEGIN
	select count(insegnamento.id_responsabile)
	from "Unidb".insegnamento
	where insegnamento.id_responsabile=new.id_responsabile 
	into n_ins_docente;
		
	IF (n_ins_docente=3)
	THEN
		RAISE EXCEPTION 'Il docente è già responsabile di 3 insegnamenti';
	END IF;
	RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_ins_insegnamento_max_3_resp_per_docente() OWNER TO postgres;

--
-- TOC entry 230 (class 1255 OID 17995)
-- Name: controllo_ins_modifica_utente_docente(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_ins_modifica_utente_docente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
	if exists(select studente.nome_utente from "Unidb".studente where studente.nome_utente=new.nome_utente)
	then
		raise exception 'Il nome utente è già stato preso';
	end if;
	if exists(select segreteria.nome_utente from "Unidb".segreteria where segreteria.nome_utente=new.nome_utente)
	then
		raise exception 'Il nome utente è già stato preso';
	end if;

return new;
end
$$;


ALTER FUNCTION public.controllo_ins_modifica_utente_docente() OWNER TO postgres;

--
-- TOC entry 231 (class 1255 OID 17997)
-- Name: controllo_ins_modifica_utente_segreteria(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_ins_modifica_utente_segreteria() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
	if exists(select docente.nome_utente from "Unidb".docente where docente.nome_utente=new.nome_utente)
	then
		raise exception 'Il nome utente è già stato preso';
	end if;
	if exists(select studente.nome_utente from "Unidb".studente where studente.nome_utente=new.nome_utente)
	then
		raise exception 'Il nome utente è già stato preso';
	end if;
	RETURN NEW;
end
$$;


ALTER FUNCTION public.controllo_ins_modifica_utente_segreteria() OWNER TO postgres;

--
-- TOC entry 246 (class 1255 OID 17975)
-- Name: controllo_ins_studente(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_ins_studente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	n_occorrenze_passate bigint;
BEGIN
	select count(storico_studente.matricola)
	from "Unidb".storico_studente
	where matricola=new.matricola
	into n_occorrenze_passate;
	
	if(n_occorrenze_passate <>0)
	then
		raise exception 'La matricola è già stata utilizzata in passato';
	end if;
	
	if exists (select segreteria.nome_utente from "Unidb".segreteria where segreteria.nome_utente=new.nome_utente)
	then
		raise exception 'Il nome utente è già stato preso';
	end if;
	if exists(select docente.nome_utente from "Unidb".docente where docente.nome_utente=new.nome_utente)
	then
		raise exception 'Il nome utente è già stato preso';
	end if;
	RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_ins_studente() OWNER TO postgres;

--
-- TOC entry 249 (class 1255 OID 17918)
-- Name: controllo_inserimento_insegnamento_in_corso_magistrale(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_inserimento_insegnamento_in_corso_magistrale() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	tipo varchar(10);
	anno smallint;
	resp_insegnamento varchar(6);
BEGIN

	select insegnamento.id_responsabile
	from "Unidb".insegnamento
	where insegnamento.id=new.id_insegnamento 
	into resp_insegnamento;
	
	IF (new.id_docente<>resp_insegnamento)
	THEN
		RAISE EXCEPTION 'Il responsabile inserito non corrisponde con insegnamento inserito';
	END IF;

	with tipo_corso as(
			select corso.tipo
			from "Unidb".corso corso
			where corso.id=new.id_corso
		),
	anno_insegnamento as(
		select insegnamento.anno
		from "Unidb".insegnamento insegnamento
		where new.id_insegnamento=insegnamento.id 
	)
	select tipo_corso.tipo,anno_insegnamento.anno
	from tipo_corso,anno_insegnamento into tipo,anno;
   IF (tipo='Magistrale' and anno=3)
   THEN
      RAISE EXCEPTION 'Insegnamento non valido per corsi magistrale';
   END IF;
   RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_inserimento_insegnamento_in_corso_magistrale() OWNER TO postgres;

--
-- TOC entry 248 (class 1255 OID 17949)
-- Name: controllo_iscrizione(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_iscrizione() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	resp_insegnamento varchar(6);
	n_occorrenze_insegnamento_in_corso bigint;
	corso_studente varchar(6);
	
	ins_propedeutico varchar(4);
	cur_propedeuticita cursor for
	select insegnamento_a
	from "Unidb".propedeuticita,"Unidb".composizione comp_a,"Unidb".composizione comp_b
	where insegnamento_b=new.id_insegnamento and comp_b.id_corso=new.id_corso
	and comp_a.id_corso=new.id_corso and insegnamento_b=comp_b.id_insegnamento
	and insegnamento_a=comp_a.id_insegnamento;
	
	voto_ultimo_esame smallint;
	
BEGIN
	select insegnamento.id_responsabile
	from "Unidb".insegnamento
	where insegnamento.id=new.id_insegnamento 
	into resp_insegnamento;
	
	IF (resp_insegnamento<>new.id_docente)
	THEN
		RAISE EXCEPTION 'Il docente inserito non è il responsabile per insegnamento inserito';
	END IF;
	
	select count(composizione.id_insegnamento)
	from "Unidb".composizione
	where id_corso=new.id_corso and id_insegnamento=new.id_insegnamento
	into n_occorrenze_insegnamento_in_corso;
	
	if(n_occorrenze_insegnamento_in_corso <>1)
	then
		raise exception 'Insegnamento non fa parte del corso inserito';
	end if;
	
	select id_corso
	from "Unidb".studente
	where matricola=new.matricola
	into corso_studente;

	if(new.id_corso<>corso_studente)
	then
		raise exception 'Lo studente non è iscritto al corso inserito';
	end if;
	
	open cur_propedeuticita;
	loop
		fetch cur_propedeuticita into ins_propedeutico;
		exit when not found;
		
		with ultimo_esame_svolto as(
		select max(data_esame) as ultima_data
		from "Unidb".carriera
		where id_insegnamento=ins_propedeutico and matricola=new.matricola
		)
		select voto
		from "Unidb".carriera,ultimo_esame_svolto
		where id_insegnamento=ins_propedeutico and matricola=new.matricola and data_esame=ultima_data
		into voto_ultimo_esame;
		if (voto_ultimo_esame<=17 or voto_ultimo_esame is null)
		then
			raise exception 'Non puoi iscriverti senza prima aver passato gli esami propedeutici';
		end if;
	end loop;	
	close cur_propedeuticita;
	RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_iscrizione() OWNER TO postgres;

--
-- TOC entry 236 (class 1255 OID 18190)
-- Name: controllo_mod_insegnamento_anno(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.controllo_mod_insegnamento_anno() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
BEGIN		
	IF exists (select tipo
	from "Unidb".corso,"Unidb".insegnamento,"Unidb".composizione
	where composizione.id_insegnamento=insegnamento.id and corso.tipo='Magistrale' and composizione.id_corso=corso.id and insegnamento.id=old.id)
	and (new.anno=3)
	THEN
		RAISE EXCEPTION 'Insegnamento fa parte di un corso magistrale: non è possibile assegnare il terzo anno';
	END IF;
	RETURN NEW;
END
$$;


ALTER FUNCTION public.controllo_mod_insegnamento_anno() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 224 (class 1259 OID 17843)
-- Name: carriera; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".carriera (
    matricola character varying(6) NOT NULL,
    id_corso character varying(6) NOT NULL,
    id_insegnamento character varying(4) NOT NULL,
    id_docente character varying(6) NOT NULL,
    data_esame date NOT NULL,
    voto smallint NOT NULL,
    CONSTRAINT voto_valido CHECK (((voto > '-1'::integer) AND (voto < 31)))
);


ALTER TABLE "Unidb".carriera OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 17755)
-- Name: composizione; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".composizione (
    id_corso character varying(6) NOT NULL,
    id_insegnamento character varying(4) NOT NULL,
    id_docente character varying(6) NOT NULL
);


ALTER TABLE "Unidb".composizione OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 17748)
-- Name: corso; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".corso (
    id character varying(6) NOT NULL,
    nome text NOT NULL,
    tipo character varying(10) NOT NULL,
    CONSTRAINT tipi_validi CHECK ((((tipo)::text = 'Triennale'::text) OR ((tipo)::text = 'Magistrale'::text)))
);


ALTER TABLE "Unidb".corso OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 17715)
-- Name: docente; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".docente (
    id character varying(6) NOT NULL,
    nome text NOT NULL,
    cognome text NOT NULL,
    nome_utente character varying(40) NOT NULL,
    password character varying(32) NOT NULL
);


ALTER TABLE "Unidb".docente OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 17781)
-- Name: esame; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".esame (
    data_esame date NOT NULL,
    id_insegnamento character varying(4) NOT NULL,
    id_docente character varying(6) NOT NULL,
    nome_esame text NOT NULL
);


ALTER TABLE "Unidb".esame OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 17722)
-- Name: insegnamento; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".insegnamento (
    id character varying(4) NOT NULL,
    id_responsabile character varying(6) NOT NULL,
    anno smallint NOT NULL,
    nome text NOT NULL,
    descrizione text,
    CONSTRAINT anni_validi CHECK (((anno > 0) AND (anno < 4)))
);


ALTER TABLE "Unidb".insegnamento OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 17889)
-- Name: iscrizione; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".iscrizione (
    matricola character varying(6) NOT NULL,
    id_corso character varying(6) NOT NULL,
    id_insegnamento character varying(4) NOT NULL,
    id_docente character varying(6) NOT NULL,
    data_esame date NOT NULL
);


ALTER TABLE "Unidb".iscrizione OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 17734)
-- Name: propedeuticita; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".propedeuticita (
    insegnamento_a character varying(4) NOT NULL,
    insegnamento_b character varying(4) NOT NULL
);


ALTER TABLE "Unidb".propedeuticita OWNER TO postgres;

--
-- TOC entry 215 (class 1259 OID 16996)
-- Name: segreteria; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".segreteria (
    nome_utente character varying(40) NOT NULL,
    password character varying(32) NOT NULL,
    sede character varying(70)
);


ALTER TABLE "Unidb".segreteria OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 17866)
-- Name: storico_carriera; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".storico_carriera (
    matricola character varying(6) NOT NULL,
    id_corso character varying(6) NOT NULL,
    id_insegnamento character varying(4) NOT NULL,
    id_docente character varying(6) NOT NULL,
    data_esame date NOT NULL,
    voto smallint NOT NULL,
    CONSTRAINT voto_valido CHECK (((voto > '-1'::integer) AND (voto < 31)))
);


ALTER TABLE "Unidb".storico_carriera OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 17831)
-- Name: storico_studente; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".storico_studente (
    matricola character varying(6) NOT NULL,
    id_corso character varying(6) NOT NULL,
    nome character varying(40) NOT NULL,
    cognome character varying(40) NOT NULL,
    nome_utente character varying(40) NOT NULL,
    password character varying(32) NOT NULL,
    anno_immatricolazione smallint
);


ALTER TABLE "Unidb".storico_studente OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 17796)
-- Name: studente; Type: TABLE; Schema: Unidb; Owner: postgres
--

CREATE TABLE "Unidb".studente (
    matricola character varying(6) NOT NULL,
    id_corso character varying(6) NOT NULL,
    nome text NOT NULL,
    cognome text NOT NULL,
    nome_utente character varying(40) NOT NULL,
    password character varying(32) NOT NULL,
    anno_immatricolazione smallint
);


ALTER TABLE "Unidb".studente OWNER TO postgres;

--
-- TOC entry 3443 (class 0 OID 17843)
-- Dependencies: 224
-- Data for Name: carriera; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".carriera (matricola, id_corso, id_insegnamento, id_docente, data_esame, voto) FROM stdin;
\.


--
-- TOC entry 3439 (class 0 OID 17755)
-- Dependencies: 220
-- Data for Name: composizione; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".composizione (id_corso, id_insegnamento, id_docente) FROM stdin;
info01	PRG1	000001
info01	PRG2	000001
info01	SIST	000001
chem01	CHIM	000002
chem01	CONT	000003
info02	COMP	000003
info01	CONT	000003
chem01	STAT	000003
\.


--
-- TOC entry 3438 (class 0 OID 17748)
-- Dependencies: 219
-- Data for Name: corso; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".corso (id, nome, tipo) FROM stdin;
info01	Informatica e Elaboratori	Triennale
info02	Algoritmi e Complessità	Magistrale
chem01	Chimica	Triennale
\.


--
-- TOC entry 3435 (class 0 OID 17715)
-- Dependencies: 216
-- Data for Name: docente; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".docente (id, nome, cognome, nome_utente, password) FROM stdin;
000001	Paolo	Rossi	Rossi_10	f0c0976535cfaf33574b5175e072bce9
000003	Paola	Vercelli	Paola	72a86026abb289634ec64d7f3b544f0c
000002	Laura	Magarini	Maga	ca2f848f64fecd2159026ef8c0d71363
000005	Giuseppe	Verdi	Verdi	71a1f456f2cca7192d736a2d529960f3
\.


--
-- TOC entry 3440 (class 0 OID 17781)
-- Dependencies: 221
-- Data for Name: esame; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".esame (data_esame, id_insegnamento, id_docente, nome_esame) FROM stdin;
2023-09-25	PRG1	000001	Esame Prog1
2023-08-10	PRG2	000001	Esame Programmazione 2
2022-01-27	PRG1	000001	Esame Programmazione 1
2023-10-09	STAT	000003	Esame Statistica
2023-07-18	CONT	000003	Esame Matematica Del Continuo
\.


--
-- TOC entry 3436 (class 0 OID 17722)
-- Dependencies: 217
-- Data for Name: insegnamento; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".insegnamento (id, id_responsabile, anno, nome, descrizione) FROM stdin;
PRG1	000001	1	Programmazione 1	Nozioni di programmazione e Utilizzo del linguaggio di programmazione GoLang
PRG2	000001	2	Programmazione 2	Nozioni sulla programmazione ad oggetti
SIST	000001	2	Sistemi Operativi	Insegnamento dedicato alle nozioni riguardanti i sistemi operativi
CHIM	000002	1	Chimica 1	Insegnamento che si concentra sulle nozioni di base e avanzate della chimica
CONT	000003	1	Matematica Del Continuo	Nozioni sulla Matematica Del Continuo: funzioni, serie numeriche...
COMP	000003	2	COMPUTING	Insegnamento atto alle nozioni relative a calcoli complessi
STAT	000003	2	Statistica e Analisi Dei Dati	Descrizione Statistica
\.


--
-- TOC entry 3445 (class 0 OID 17889)
-- Dependencies: 226
-- Data for Name: iscrizione; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".iscrizione (matricola, id_corso, id_insegnamento, id_docente, data_esame) FROM stdin;
\.


--
-- TOC entry 3437 (class 0 OID 17734)
-- Dependencies: 218
-- Data for Name: propedeuticita; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".propedeuticita (insegnamento_a, insegnamento_b) FROM stdin;
PRG1	PRG2
PRG1	SIST
CONT	STAT
\.


--
-- TOC entry 3434 (class 0 OID 16996)
-- Dependencies: 215
-- Data for Name: segreteria; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".segreteria (nome_utente, password, sede) FROM stdin;
Sede_info	e84533c062abaa7cea4bed1009de8196	Via Celoria 18
\.


--
-- TOC entry 3444 (class 0 OID 17866)
-- Dependencies: 225
-- Data for Name: storico_carriera; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".storico_carriera (matricola, id_corso, id_insegnamento, id_docente, data_esame, voto) FROM stdin;
986561	info01	PRG1	000001	2023-09-25	22
986561	info01	PRG1	000001	2022-01-27	10
\.


--
-- TOC entry 3442 (class 0 OID 17831)
-- Dependencies: 223
-- Data for Name: storico_studente; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".storico_studente (matricola, id_corso, nome, cognome, nome_utente, password, anno_immatricolazione) FROM stdin;
986561	info01	Dani	Bond	DaniBond	11a98374ebec8e0c7a54751d2161804d	2021
\.


--
-- TOC entry 3441 (class 0 OID 17796)
-- Dependencies: 222
-- Data for Name: studente; Type: TABLE DATA; Schema: Unidb; Owner: postgres
--

COPY "Unidb".studente (matricola, id_corso, nome, cognome, nome_utente, password, anno_immatricolazione) FROM stdin;
123123	info02	Giacomo	Gialli	Gialli	dd1a1e2287c3d66dc73c4ca40ec2645d	2020
\.


--
-- TOC entry 3260 (class 2606 OID 18195)
-- Name: carriera carriera_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".carriera
    ADD CONSTRAINT carriera_pkey PRIMARY KEY (matricola, id_corso, id_insegnamento, id_docente, data_esame);


--
-- TOC entry 3250 (class 2606 OID 17994)
-- Name: composizione composizione_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".composizione
    ADD CONSTRAINT composizione_pkey PRIMARY KEY (id_docente, id_insegnamento, id_corso);


--
-- TOC entry 3246 (class 2606 OID 18205)
-- Name: corso corso_nome_key; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".corso
    ADD CONSTRAINT corso_nome_key UNIQUE (nome);


--
-- TOC entry 3248 (class 2606 OID 17752)
-- Name: corso corso_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".corso
    ADD CONSTRAINT corso_pkey PRIMARY KEY (id);


--
-- TOC entry 3236 (class 2606 OID 17721)
-- Name: docente docente_nome_utente_key; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".docente
    ADD CONSTRAINT docente_nome_utente_key UNIQUE (nome_utente);


--
-- TOC entry 3238 (class 2606 OID 17719)
-- Name: docente docente_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".docente
    ADD CONSTRAINT docente_pkey PRIMARY KEY (id);


--
-- TOC entry 3252 (class 2606 OID 18169)
-- Name: esame esame_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".esame
    ADD CONSTRAINT esame_pkey PRIMARY KEY (data_esame, id_insegnamento, id_docente);


--
-- TOC entry 3240 (class 2606 OID 18201)
-- Name: insegnamento insegnamento_nome_key; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".insegnamento
    ADD CONSTRAINT insegnamento_nome_key UNIQUE (nome);


--
-- TOC entry 3242 (class 2606 OID 17726)
-- Name: insegnamento insegnamento_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".insegnamento
    ADD CONSTRAINT insegnamento_pkey PRIMARY KEY (id);


--
-- TOC entry 3264 (class 2606 OID 18007)
-- Name: iscrizione iscrizione_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".iscrizione
    ADD CONSTRAINT iscrizione_pkey PRIMARY KEY (matricola, id_corso, id_insegnamento, id_docente, data_esame);


--
-- TOC entry 3244 (class 2606 OID 17962)
-- Name: propedeuticita propedeuticita_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".propedeuticita
    ADD CONSTRAINT propedeuticita_pkey PRIMARY KEY (insegnamento_a, insegnamento_b);


--
-- TOC entry 3234 (class 2606 OID 17002)
-- Name: segreteria segreteria_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".segreteria
    ADD CONSTRAINT segreteria_pkey PRIMARY KEY (nome_utente);


--
-- TOC entry 3262 (class 2606 OID 18272)
-- Name: storico_carriera storico_carriera_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".storico_carriera
    ADD CONSTRAINT storico_carriera_pkey PRIMARY KEY (matricola, id_corso, id_insegnamento, id_docente, data_esame);


--
-- TOC entry 3258 (class 2606 OID 18274)
-- Name: storico_studente storico_studente_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".storico_studente
    ADD CONSTRAINT storico_studente_pkey PRIMARY KEY (matricola, id_corso);


--
-- TOC entry 3254 (class 2606 OID 17802)
-- Name: studente studente_nome_utente_key; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".studente
    ADD CONSTRAINT studente_nome_utente_key UNIQUE (nome_utente);


--
-- TOC entry 3256 (class 2606 OID 18226)
-- Name: studente studente_pkey; Type: CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".studente
    ADD CONSTRAINT studente_pkey PRIMARY KEY (matricola);


--
-- TOC entry 3289 (class 2620 OID 17987)
-- Name: carriera arch_carriera; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER arch_carriera BEFORE DELETE ON "Unidb".carriera FOR EACH ROW EXECUTE FUNCTION public.archiviazione_carriera();


--
-- TOC entry 3287 (class 2620 OID 17978)
-- Name: studente cancellazione_studente; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER cancellazione_studente BEFORE DELETE ON "Unidb".studente FOR EACH ROW EXECUTE FUNCTION public.archiviazione_studente();


--
-- TOC entry 3290 (class 2620 OID 17986)
-- Name: carriera ins_carriera; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER ins_carriera BEFORE INSERT ON "Unidb".carriera FOR EACH ROW EXECUTE FUNCTION public.controllo_ins_carriera();


--
-- TOC entry 3284 (class 2620 OID 17919)
-- Name: composizione ins_composizione; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER ins_composizione BEFORE INSERT ON "Unidb".composizione FOR EACH ROW EXECUTE FUNCTION public.controllo_inserimento_insegnamento_in_corso_magistrale();


--
-- TOC entry 3285 (class 2620 OID 18210)
-- Name: esame ins_esame; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER ins_esame BEFORE INSERT OR UPDATE ON "Unidb".esame FOR EACH ROW EXECUTE FUNCTION public.controllo_ins_data_anno_insegnamento();


--
-- TOC entry 3286 (class 2620 OID 18192)
-- Name: esame ins_esame_resp_corretto; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER ins_esame_resp_corretto BEFORE INSERT ON "Unidb".esame FOR EACH ROW EXECUTE FUNCTION public.controllo_ins_esame_resp_corretto();


--
-- TOC entry 3282 (class 2620 OID 17945)
-- Name: insegnamento ins_insegnamento_max_3_resp; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER ins_insegnamento_max_3_resp BEFORE INSERT ON "Unidb".insegnamento FOR EACH ROW EXECUTE FUNCTION public.controllo_ins_insegnamento_max_3_resp_per_docente();


--
-- TOC entry 3291 (class 2620 OID 18193)
-- Name: iscrizione ins_iscrizione; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER ins_iscrizione BEFORE INSERT ON "Unidb".iscrizione FOR EACH ROW EXECUTE FUNCTION public.controllo_iscrizione();


--
-- TOC entry 3288 (class 2620 OID 17976)
-- Name: studente ins_studente; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER ins_studente BEFORE INSERT OR UPDATE ON "Unidb".studente FOR EACH ROW EXECUTE FUNCTION public.controllo_ins_studente();


--
-- TOC entry 3283 (class 2620 OID 18191)
-- Name: insegnamento mod_insegnamento_anno; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER mod_insegnamento_anno BEFORE UPDATE ON "Unidb".insegnamento FOR EACH ROW EXECUTE FUNCTION public.controllo_mod_insegnamento_anno();


--
-- TOC entry 3281 (class 2620 OID 17996)
-- Name: docente utente_docente; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER utente_docente BEFORE INSERT OR UPDATE ON "Unidb".docente FOR EACH ROW EXECUTE FUNCTION public.controllo_ins_modifica_utente_docente();


--
-- TOC entry 3280 (class 2620 OID 17998)
-- Name: segreteria utente_segreteria; Type: TRIGGER; Schema: Unidb; Owner: postgres
--

CREATE TRIGGER utente_segreteria BEFORE INSERT OR UPDATE ON "Unidb".segreteria FOR EACH ROW EXECUTE FUNCTION public.controllo_ins_modifica_utente_segreteria();


--
-- TOC entry 3274 (class 2606 OID 18251)
-- Name: carriera carriera_id_corso_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".carriera
    ADD CONSTRAINT carriera_id_corso_fkey FOREIGN KEY (id_corso) REFERENCES "Unidb".corso(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3275 (class 2606 OID 18185)
-- Name: carriera carriera_id_insegnamento_id_docente_data_esame_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".carriera
    ADD CONSTRAINT carriera_id_insegnamento_id_docente_data_esame_fkey FOREIGN KEY (id_docente, id_insegnamento, data_esame) REFERENCES "Unidb".esame(id_docente, id_insegnamento, data_esame) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3276 (class 2606 OID 18256)
-- Name: carriera carriera_matricola_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".carriera
    ADD CONSTRAINT carriera_matricola_fkey FOREIGN KEY (matricola) REFERENCES "Unidb".studente(matricola) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3268 (class 2606 OID 18163)
-- Name: composizione composizione_id_corso_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".composizione
    ADD CONSTRAINT composizione_id_corso_fkey FOREIGN KEY (id_corso) REFERENCES "Unidb".corso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3269 (class 2606 OID 18043)
-- Name: composizione composizione_id_docente_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".composizione
    ADD CONSTRAINT composizione_id_docente_fkey FOREIGN KEY (id_docente) REFERENCES "Unidb".docente(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3270 (class 2606 OID 18068)
-- Name: composizione composizione_id_insegnamento_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".composizione
    ADD CONSTRAINT composizione_id_insegnamento_fkey FOREIGN KEY (id_insegnamento) REFERENCES "Unidb".insegnamento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3271 (class 2606 OID 18053)
-- Name: esame esame_id_docente_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".esame
    ADD CONSTRAINT esame_id_docente_fkey FOREIGN KEY (id_docente) REFERENCES "Unidb".docente(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3272 (class 2606 OID 18063)
-- Name: esame esame_id_insegnamento_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".esame
    ADD CONSTRAINT esame_id_insegnamento_fkey FOREIGN KEY (id_insegnamento) REFERENCES "Unidb".insegnamento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3265 (class 2606 OID 18048)
-- Name: insegnamento insegnamento_id_responsabile_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".insegnamento
    ADD CONSTRAINT insegnamento_id_responsabile_fkey FOREIGN KEY (id_responsabile) REFERENCES "Unidb".docente(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3277 (class 2606 OID 18148)
-- Name: iscrizione iscrizione_id_corso_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".iscrizione
    ADD CONSTRAINT iscrizione_id_corso_fkey FOREIGN KEY (id_corso) REFERENCES "Unidb".corso(id) ON DELETE CASCADE;


--
-- TOC entry 3278 (class 2606 OID 18175)
-- Name: iscrizione iscrizione_id_docente_id_insegnamento_data_esame_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".iscrizione
    ADD CONSTRAINT iscrizione_id_docente_id_insegnamento_data_esame_fkey FOREIGN KEY (id_docente, id_insegnamento, data_esame) REFERENCES "Unidb".esame(id_docente, id_insegnamento, data_esame) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3279 (class 2606 OID 18266)
-- Name: iscrizione iscrizione_matricola_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".iscrizione
    ADD CONSTRAINT iscrizione_matricola_fkey FOREIGN KEY (matricola) REFERENCES "Unidb".studente(matricola) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3266 (class 2606 OID 18083)
-- Name: propedeuticita propedeuticita_insegnamento_a_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".propedeuticita
    ADD CONSTRAINT propedeuticita_insegnamento_a_fkey FOREIGN KEY (insegnamento_a) REFERENCES "Unidb".insegnamento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3267 (class 2606 OID 18088)
-- Name: propedeuticita propedeuticita_insegnamento_b_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".propedeuticita
    ADD CONSTRAINT propedeuticita_insegnamento_b_fkey FOREIGN KEY (insegnamento_b) REFERENCES "Unidb".insegnamento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3273 (class 2606 OID 18158)
-- Name: studente studente_id_corso_fkey; Type: FK CONSTRAINT; Schema: Unidb; Owner: postgres
--

ALTER TABLE ONLY "Unidb".studente
    ADD CONSTRAINT studente_id_corso_fkey FOREIGN KEY (id_corso) REFERENCES "Unidb".corso(id) ON UPDATE CASCADE ON DELETE CASCADE;


-- Completed on 2023-06-23 16:20:33

--
-- PostgreSQL database dump complete
--

