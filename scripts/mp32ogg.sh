#!/bin/bash
for I in ../public/sound/*.mp3
do
	ogg=$(echo $I | sed -e s/mp3/ogg/)
	if [ ! -f $ogg ]; then
		ffmpeg -i $I -codec:a libvorbis -q:a 3 $ogg
	fi
done

for I in ../public/sound/background/*.mp3
do
        ogg=$(echo $I | sed -e s/mp3/ogg/)
        if [ ! -f $ogg ]; then
                ffmpeg -i $I -codec:a libvorbis -q:a 3 $ogg
        fi
done
