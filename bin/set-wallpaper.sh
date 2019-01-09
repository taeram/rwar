#!/bin/bash

if [ -z "$1" ]; then
    echo "Usage: $( basename $0 ) <image file> <subreddit name> <system wallpaper file>"
    exit 1
fi
IMAGE_FILE=$1
SUBREDDIT=$2
WALLPAPER_FILE=$3

# fail fast
set -e -u

function mktemp_suffix() {
    local SUFFIX=$1

    local CMD=mktemp
    if [[ "$IS_OSX" ]]; then
      CMD=gmktemp
    fi

    ${CMD} --suffix=$SUFFIX
}

IS_OSX=$( echo $OSTYPE | grep -i darwin | wc -l )
if [[ "$IS_OSX" ]]; then
    SCRIPT_DIR=$( cd $(dirname $0) && pwd -P )

    if [[ -z $( which gmktemp ) ]]; then
        echo "Please install coreutils: brew install coreutils"
        exit 1
    fi
else
    SCRIPT_DIR=$( dirname $(readlink -f $0) )
fi

if [[ -z "$( which convert )" ]]; then
    echo "Installing ImageMagick"
    if [[ "$IS_OSX" ]]; then
        brew install imagemagick
    else
        sudo apt-get install -y imagemagick
    fi
fi

# Resize (and crop, if necessary) the image to fit the primary desktop
if [[ "$IS_OSX" ]]; then
    DESKTOP_RESOLUTION=$( system_profiler SPDisplaysDataType | grep Resolution | head -1 | sed -e 's/^ *//' -e 's/Resolution: //' -e 's/ [A-Za-z]*$//' -e 's/ x /x/' )
else
    # Linux
    DESKTOP_RESOLUTION=$( xrandr | head -n2 | tail -n1 | awk '{print $4}' | sed -e 's/\+.*$//' )
fi

echo "    * Resizing wallpaper to $DESKTOP_RESOLUTION"
IMAGE_FILE_RESIZED=$( mktemp_suffix .jpg )
convert "${IMAGE_FILE}" -geometry $DESKTOP_RESOLUTION^ -gravity center -crop ${DESKTOP_RESOLUTION}+0+0 "$IMAGE_FILE_RESIZED"

if [[ ! -e $IMAGE_FILE_RESIZED ]]; then
    echo "    !! Could not resize image, exiting..."
    continue
fi

# Add the subreddit name to the image
echo "    * Adding /r/$SUBREDDIT watermark"
DESKTOP_WIDTH=$( echo "$DESKTOP_RESOLUTION" | sed -e 's/x.*$//' )
FONT_SIZE=$( expr $DESKTOP_WIDTH / 128 )
if [[ "$IS_OSX" ]]; then
    TEXT_OFFSET="+3+3"
else
    TEXT_OFFSET="+5+30"
fi
IS_WATERMARKED=$( mogrify -fill \#999 -pointsize $FONT_SIZE -gravity southwest -annotate $TEXT_OFFSET "/r/$SUBREDDIT" "$IMAGE_FILE_RESIZED" )

mv "$IMAGE_FILE_RESIZED" "$WALLPAPER_FILE"
