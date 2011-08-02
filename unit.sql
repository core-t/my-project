--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: unit_unitId_seq; Type: SEQUENCE SET; Schema: public; Owner: warlords
--

SELECT pg_catalog.setval('"unit_unitId_seq"', 10, true);


--
-- Data for Name: unit; Type: TABLE DATA; Schema: public; Owner: warlords
--

COPY unit ("unitId", name, "numberOfMoves", "attackPoints", "defensePoints", "canFly", "canSwim", "modMovesForest", "modMovesGrass", "modMovesSwamp", "modMovesMountains", "modMovesWater", "modAttackForest", "modAttackGrass", "modAttackSwamp", "modAttackMountains", "modAttackWater", "modDefenseForest", "modDefenseGrass", "modDefenseSwamp", "modDefenseMountains", "modDefenseWater", cost) FROM stdin;
1	Light Infantry	10	3	3	f	f	3	2	3	4	0	0	0	0	0	0	0	0	0	0	0	4
2	Heavy Infantry	8	5	5	f	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	4
3	Cavalry	16	6	4	f	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	8
4	Giants	10	6	6	f	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	4
5	Wolves	14	5	5	f	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	8
6	Navy	18	5	4	f	t	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	20
7	Archers	12	4	4	f	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	4
8	Pegasi	16	4	4	t	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	16
9	Dwarves	9	5	5	f	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	4
10	Griffins	18	6	5	t	f	0	0	0	0	0	0	0	0	0	0	0	0	0	0	0	16
\.


--
-- PostgreSQL database dump complete
--

