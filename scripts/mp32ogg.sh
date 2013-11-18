#!/bin/bash
for I in ../public/sound/*.mp3
do
	ogg=$(echo $I | sed -e s/mp3/ogg/)
	ffmpeg -i $I -codec:a libvorbis -q:a 3 $ogg
done
