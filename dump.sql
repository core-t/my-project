--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: postgres
--

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;


ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO postgres;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: army; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE army (
    "armyId" integer NOT NULL,
    "playerId" integer NOT NULL,
    "position" point NOT NULL,
    "gameId" integer NOT NULL,
    destroyed boolean DEFAULT false NOT NULL
);


ALTER TABLE public.army OWNER TO warlords;

--
-- Name: army_armyId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "army_armyId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."army_armyId_seq" OWNER TO warlords;

--
-- Name: army_armyId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "army_armyId_seq" OWNED BY army."armyId";


--
-- Name: artefact; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE artefact (
    "artefactId" integer NOT NULL,
    name character varying(256),
    description text,
    image character varying(256),
    probability integer NOT NULL,
    "canFly" boolean DEFAULT false NOT NULL,
    "canSwim" boolean DEFAULT false NOT NULL,
    "modMovesForest" integer DEFAULT 0 NOT NULL,
    "modMovesGrass" integer DEFAULT 0 NOT NULL,
    "modMovesSwamp" integer DEFAULT 0 NOT NULL,
    "modMovesMountains" integer DEFAULT 0 NOT NULL,
    "modMovesWater" integer DEFAULT 0 NOT NULL,
    "modAttackForest" integer DEFAULT 0 NOT NULL,
    "modAttackGrass" integer DEFAULT 0 NOT NULL,
    "modAttackSwamp" integer DEFAULT 0 NOT NULL,
    "modAttackMountains" integer DEFAULT 0 NOT NULL,
    "modAttackWater" integer DEFAULT 0 NOT NULL,
    "modDefenseForest" integer DEFAULT 0 NOT NULL,
    "modDefenseGrass" integer DEFAULT 0 NOT NULL,
    "modDefenseSwamp" integer DEFAULT 0 NOT NULL,
    "modDefenseMountains" integer DEFAULT 0 NOT NULL,
    "modDefenseWater" integer DEFAULT 0 NOT NULL,
    "modMoves" integer DEFAULT 0 NOT NULL,
    "modAttack" integer DEFAULT 0 NOT NULL,
    "modDefense" integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.artefact OWNER TO warlords;

--
-- Name: artefact_artefactId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "artefact_artefactId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."artefact_artefactId_seq" OWNER TO warlords;

--
-- Name: artefact_artefactId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "artefact_artefactId_seq" OWNED BY artefact."artefactId";


--
-- Name: castle; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE castle (
    "castleId" integer NOT NULL,
    "playerId" integer NOT NULL,
    "gameId" integer NOT NULL,
    production integer,
    "productionTurn" integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.castle OWNER TO warlords;

--
-- Name: castle_castleId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "castle_castleId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."castle_castleId_seq" OWNER TO warlords;

--
-- Name: castle_castleId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "castle_castleId_seq" OWNED BY castle."castleId";


--
-- Name: game; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE game (
    "gameId" integer NOT NULL,
    "isActive" boolean DEFAULT true NOT NULL,
    "isOpen" boolean DEFAULT true NOT NULL,
    "numberOfPlayers" integer NOT NULL,
    "gameMasterId" integer NOT NULL,
    "turnPlayerId" integer,
    begin timestamp without time zone DEFAULT now(),
    "turnNumber" integer DEFAULT 0
);


ALTER TABLE public.game OWNER TO warlords;

--
-- Name: game_gameId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "game_gameId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."game_gameId_seq" OWNER TO warlords;

--
-- Name: game_gameId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "game_gameId_seq" OWNED BY game."gameId";


--
-- Name: hero; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE hero (
    "heroId" integer NOT NULL,
    "playerId" integer NOT NULL,
    "numberOfMoves" integer DEFAULT 12 NOT NULL,
    "attackPoints" integer DEFAULT 6 NOT NULL,
    "defensePoints" integer DEFAULT 6 NOT NULL,
    "armyId" integer,
    experience integer DEFAULT 0,
    "gameId" integer,
    "movesLeft" integer NOT NULL
);


ALTER TABLE public.hero OWNER TO warlords;

--
-- Name: hero_heroId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "hero_heroId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."hero_heroId_seq" OWNER TO warlords;

--
-- Name: hero_heroId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "hero_heroId_seq" OWNED BY hero."heroId";


--
-- Name: hero_movesLeft_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "hero_movesLeft_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."hero_movesLeft_seq" OWNER TO warlords;

--
-- Name: hero_movesLeft_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "hero_movesLeft_seq" OWNED BY hero."movesLeft";


--
-- Name: inventory; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE inventory (
    "heroId" integer NOT NULL,
    "artefactId" integer NOT NULL
);


ALTER TABLE public.inventory OWNER TO warlords;

--
-- Name: player; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE player (
    "fbId" character varying(256) NOT NULL,
    "playerId" integer NOT NULL,
    activity timestamp without time zone DEFAULT now(),
    login character varying,
    password character varying,
    "isLogged" boolean DEFAULT false
);


ALTER TABLE public.player OWNER TO warlords;

--
-- Name: player_playerId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "player_playerId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."player_playerId_seq" OWNER TO warlords;

--
-- Name: player_playerId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "player_playerId_seq" OWNED BY player."playerId";


--
-- Name: playersingame; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE playersingame (
    "gameId" integer NOT NULL,
    "playerId" integer NOT NULL,
    color character varying,
    timeout timestamp without time zone DEFAULT now() NOT NULL,
    ready boolean DEFAULT false NOT NULL,
    "turnActive" boolean DEFAULT false NOT NULL,
    gold integer DEFAULT 0 NOT NULL,
    lost boolean DEFAULT false NOT NULL
);


ALTER TABLE public.playersingame OWNER TO warlords;

--
-- Name: production; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE production (
    "castleId" integer NOT NULL,
    "unitId" integer NOT NULL,
    "numberOfTurns" integer DEFAULT 1 NOT NULL,
    "order" integer NOT NULL
);


ALTER TABLE public.production OWNER TO warlords;

--
-- Name: soldier; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE soldier (
    "soldierId" integer NOT NULL,
    "armyId" integer NOT NULL,
    "unitId" integer NOT NULL,
    experience integer DEFAULT 0 NOT NULL,
    "gameId" integer,
    "movesLeft" integer DEFAULT 0
);


ALTER TABLE public.soldier OWNER TO warlords;

--
-- Name: soldier_soldierId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "soldier_soldierId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."soldier_soldierId_seq" OWNER TO warlords;

--
-- Name: soldier_soldierId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "soldier_soldierId_seq" OWNED BY soldier."soldierId";


--
-- Name: unit; Type: TABLE; Schema: public; Owner: warlords; Tablespace: 
--

CREATE TABLE unit (
    "unitId" integer NOT NULL,
    name character varying,
    "numberOfMoves" integer NOT NULL,
    "attackPoints" integer NOT NULL,
    "defensePoints" integer NOT NULL,
    "canFly" boolean DEFAULT false NOT NULL,
    "canSwim" boolean DEFAULT false NOT NULL,
    "modMovesForest" integer DEFAULT 0 NOT NULL,
    "modMovesGrass" integer DEFAULT 0 NOT NULL,
    "modMovesSwamp" integer DEFAULT 0 NOT NULL,
    "modMovesMountains" integer DEFAULT 0 NOT NULL,
    "modMovesWater" integer DEFAULT 0 NOT NULL,
    "modAttackForest" integer DEFAULT 0 NOT NULL,
    "modAttackGrass" integer DEFAULT 0 NOT NULL,
    "modAttackSwamp" integer DEFAULT 0 NOT NULL,
    "modAttackMountains" integer DEFAULT 0 NOT NULL,
    "modAttackWater" integer DEFAULT 0 NOT NULL,
    "modDefenseForest" integer DEFAULT 0 NOT NULL,
    "modDefenseGrass" integer DEFAULT 0 NOT NULL,
    "modDefenseSwamp" integer DEFAULT 0 NOT NULL,
    "modDefenseMountains" integer DEFAULT 0 NOT NULL,
    "modDefenseWater" integer DEFAULT 0 NOT NULL,
    cost integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.unit OWNER TO warlords;

--
-- Name: unit_unitId_seq; Type: SEQUENCE; Schema: public; Owner: warlords
--

CREATE SEQUENCE "unit_unitId_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."unit_unitId_seq" OWNER TO warlords;

--
-- Name: unit_unitId_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: warlords
--

ALTER SEQUENCE "unit_unitId_seq" OWNED BY unit."unitId";


--
-- Name: artefactId; Type: DEFAULT; Schema: public; Owner: warlords
--

ALTER TABLE artefact ALTER COLUMN "artefactId" SET DEFAULT nextval('"artefact_artefactId_seq"'::regclass);


--
-- Name: gameId; Type: DEFAULT; Schema: public; Owner: warlords
--

ALTER TABLE game ALTER COLUMN "gameId" SET DEFAULT nextval('"game_gameId_seq"'::regclass);


--
-- Name: heroId; Type: DEFAULT; Schema: public; Owner: warlords
--

ALTER TABLE hero ALTER COLUMN "heroId" SET DEFAULT nextval('"hero_heroId_seq"'::regclass);


--
-- Name: movesLeft; Type: DEFAULT; Schema: public; Owner: warlords
--

ALTER TABLE hero ALTER COLUMN "movesLeft" SET DEFAULT nextval('"hero_movesLeft_seq"'::regclass);


--
-- Name: playerId; Type: DEFAULT; Schema: public; Owner: warlords
--

ALTER TABLE player ALTER COLUMN "playerId" SET DEFAULT nextval('"player_playerId_seq"'::regclass);


--
-- Name: soldierId; Type: DEFAULT; Schema: public; Owner: warlords
--

ALTER TABLE soldier ALTER COLUMN "soldierId" SET DEFAULT nextval('"soldier_soldierId_seq"'::regclass);


--
-- Name: unitId; Type: DEFAULT; Schema: public; Owner: warlords
--

ALTER TABLE unit ALTER COLUMN "unitId" SET DEFAULT nextval('"unit_unitId_seq"'::regclass);


--
-- Name: army_pkey; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY army
    ADD CONSTRAINT army_pkey PRIMARY KEY ("armyId", "gameId");


--
-- Name: artefact_pkey; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY artefact
    ADD CONSTRAINT artefact_pkey PRIMARY KEY ("artefactId");


--
-- Name: castle_castleId_gameId_key; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY castle
    ADD CONSTRAINT "castle_castleId_gameId_key" UNIQUE ("castleId", "gameId");


--
-- Name: game_pkey; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY game
    ADD CONSTRAINT game_pkey PRIMARY KEY ("gameId");


--
-- Name: hero_pkey; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY hero
    ADD CONSTRAINT hero_pkey PRIMARY KEY ("heroId");


--
-- Name: inventory_heroId_artefactId_key; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY inventory
    ADD CONSTRAINT "inventory_heroId_artefactId_key" UNIQUE ("heroId", "artefactId");


--
-- Name: player_fbId_key; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY player
    ADD CONSTRAINT "player_fbId_key" UNIQUE ("fbId");


--
-- Name: player_pkey; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY player
    ADD CONSTRAINT player_pkey PRIMARY KEY ("playerId");


--
-- Name: playersingame_gameId_playerId_key; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY playersingame
    ADD CONSTRAINT "playersingame_gameId_playerId_key" UNIQUE ("gameId", "playerId");


--
-- Name: soldier_pkey; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY soldier
    ADD CONSTRAINT soldier_pkey PRIMARY KEY ("soldierId");


--
-- Name: unit_pkey; Type: CONSTRAINT; Schema: public; Owner: warlords; Tablespace: 
--

ALTER TABLE ONLY unit
    ADD CONSTRAINT unit_pkey PRIMARY KEY ("unitId");


--
-- Name: army_gameId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY army
    ADD CONSTRAINT "army_gameId_fkey" FOREIGN KEY ("gameId") REFERENCES game("gameId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: army_playerId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY army
    ADD CONSTRAINT "army_playerId_fkey" FOREIGN KEY ("playerId") REFERENCES player("playerId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: castle_gameId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY castle
    ADD CONSTRAINT "castle_gameId_fkey" FOREIGN KEY ("gameId") REFERENCES game("gameId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: castle_playerId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY castle
    ADD CONSTRAINT "castle_playerId_fkey" FOREIGN KEY ("playerId") REFERENCES player("playerId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: castle_production_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY castle
    ADD CONSTRAINT castle_production_fkey FOREIGN KEY (production) REFERENCES unit("unitId") ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: game_playerId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY game
    ADD CONSTRAINT "game_playerId_fkey" FOREIGN KEY ("gameMasterId") REFERENCES player("playerId");


--
-- Name: game_turnPlayerId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY game
    ADD CONSTRAINT "game_turnPlayerId_fkey" FOREIGN KEY ("turnPlayerId") REFERENCES player("playerId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hero_armyId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY hero
    ADD CONSTRAINT "hero_armyId_fkey" FOREIGN KEY ("armyId", "gameId") REFERENCES army("armyId", "gameId") ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: hero_playerId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY hero
    ADD CONSTRAINT "hero_playerId_fkey" FOREIGN KEY ("playerId") REFERENCES player("playerId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: inventory_artefactId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY inventory
    ADD CONSTRAINT "inventory_artefactId_fkey" FOREIGN KEY ("artefactId") REFERENCES artefact("artefactId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: inventory_heroId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY inventory
    ADD CONSTRAINT "inventory_heroId_fkey" FOREIGN KEY ("heroId") REFERENCES hero("heroId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: playersingame_gameId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY playersingame
    ADD CONSTRAINT "playersingame_gameId_fkey" FOREIGN KEY ("gameId") REFERENCES game("gameId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: playersingame_playerId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY playersingame
    ADD CONSTRAINT "playersingame_playerId_fkey" FOREIGN KEY ("playerId") REFERENCES player("playerId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: production_unitId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY production
    ADD CONSTRAINT "production_unitId_fkey" FOREIGN KEY ("unitId") REFERENCES unit("unitId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: soldier_armyId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY soldier
    ADD CONSTRAINT "soldier_armyId_fkey" FOREIGN KEY ("armyId", "gameId") REFERENCES army("armyId", "gameId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: soldier_unitId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: warlords
--

ALTER TABLE ONLY soldier
    ADD CONSTRAINT "soldier_unitId_fkey" FOREIGN KEY ("unitId") REFERENCES unit("unitId") ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

