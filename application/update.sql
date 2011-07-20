--Activity
ALTER TABLE player ADD activity timestamp DEFAULT now();

--Registration
ALTER TABLE player ADD login varchar;
ALTER TABLE player ADD password varchar;
ALTER TABLE player ADD "isLogged" boolean DEFAULT false;
--Turn activation
ALTER TABLE playersingame ADD "turnActive" boolean DEFAULT false;
--Default Units
INSERT INTO unit VALUES (1, '', 'light_infantry', 10, 5, 4, false, false, 3, 2, 3, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
--Unique units identyfication
ALTER TABLE hero ADD "gameId" integer NOT NULL;
ALTER TABLE soldier ADD "gameId" integer NOT NULL;
