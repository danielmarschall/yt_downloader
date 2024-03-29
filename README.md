
# ViaThinkSoft YouTube Downloader 2.4.1

YouTube Downloader is a tool for Linux. It is a wrapper for youtube-dl (or any compatible forks) and offers several additional functionalities.

Special features:
- Downloading of all videos of a channel or a playlist.
- Automatic searching inside channels or globally (whole YouTube)
- You can download videos and audio files.
- YouTube-IDs can be automatically written in the ID tag of downloaded mp3 files.
- An automatically managed list of already downloaded videos allows you to move away from the downloaded files without the risk of downloading the already downloaded files again.
- An automatically managed list of failed downloads will avoid that a video, which is not available anymore, is tried to be downloaded too many times.
- Creation of SFV and/or MD5 checksum files.
- The tool is fully CLI and is optimized for cronjobs.

## Syntax

    ./ytdwn [-t|--type v:[ext]|a:[ext]] Type video or audio and preferred output type, e.g. 'a:mp3' (default v:)
            [-o|--outputDir <dir>]      Default current working directory
            [-a|--alreadyDownloaded <file>]
            [-f|--failList <file> <treshold>]  This file logs failures.
            [-F|--failTreshold <num>]   Don't download if failure (-f) treshold is reached. (Default: 3)
            [-V|--version]              Shows the version
            [-v|--verbose]              Displays verbose information to STDOUT
            [-h|--help]                 Shows the help page.
            [-N|--no-mp3-tagtransfer]   Disables transfer of video ID to MP3 ID tag.
                                        This feature requires the package "id3v2".
            [-H|--checksumMode]         Which checksum files shall be written for new files.
                                        Must be 'None', 'MD5', 'SFV', or 'MD5,SFV'.
            [-T|--default-template <t>] Sets default filename template.
                                        (Default: '%(title)s-%(id)s.%(ext)s')
            [-X|--extra-args <args>]    Additional arguments passed through youtube-dl. (Default "-ic")
            [-A|--api-key <file|key>]   Specifies the API key, or a file containing the API key
                                        (Default: ~/.yt_api_key)
            [--cookies=<file>]          A netscape compatible cookie file (for age restricted videos
                                        (Default: ~/.yt_cookkies)
            [-C|--resultcache <file>]   Allows video results to be cached in this file;
                                        only for playlists or channels.
            [-O|--create-outputdir]     Allows creation of the output directories, recursively.
            [--downloader=exename]      Binary file name of a youtube-dl compatible tool.
                                        Currently supported/tested: youtube-dl, youtube-dlc, yt-dlp.
            [--]
            <resource> [<resource> ...]

For all paths (outputDir, alreadyDownloaded, apikey, failList and resultcache), you can use the
term '[listname]' which will be replaced by the basename of the current list file (without file extension).
For example you can do following:

    ./ytdwn -o 'downloads/[listname]' -- list:*.list

If no list file is processed, it will be replaced with nothing.

The "alreadyDownloaded" argument contains a file which will be managed by ytdwn.
It will contain all video IDs which have been downloaded. This allows you to
move away the already downloaded files, and ytdwn will not download them again.

Examples for type:
- `v:` = best video quality
- `a:` = best audio only
- `a:mp3` = audio only, mp3
- Valid audio formats according to "man youtube-dl" are
  "best", "aac", "flac", "mp3", "m4a", "opus", "vorbis", or "wav"; "best" by default

A `<resource>` can be one of the following:

    vid:<video ID>
    vurl:<youtube video URL>
    pid:<playlist ID>
    purl:<playlist URL>
    cid:<channel id>
    cname:<channel name>
    curl:<channel or username URL>
    list:<file with resource entries>   (comments can be #)
    search:<searchterm>

For channels (`cid`, `cname`, `curl`) you can also perform a search to filter the results.
This can be done like this: `cname:[search="Elvis Presley"]channel_1234`
For the search option, following parameters are possible: `search:[order=date][maxresults=50]"Elvis Presley"`
Acceptable order values are: `date`, `rating`, `relevance`, `title`, `videoCount`, `viewCount`
Default values are `order=relevance` and `maxresults=10`
Use `maxresults=-1` to download everything which matches the searchterm.

## Requirements
- PHP CLI
- A YouTube API key (can be obtained here: https://console.developers.google.com/apis/credentials )
- If you want to extract audio, you need additionally: ffmpeg or avconv and ffprobe or avprobe.
- Optional: package "id3v2" to allow the YouTube video id to be transferred to the MP3 ID tag

## Age restricted videos how-to

To download age restricted videos, you need to supply cookies from a browser that has been logged in to YouTube.

**Here is a method how to do this:**

(1) Download and install this Chrome extension:
https://chrome.google.com/webstore/detail/cookiestxt/njabckikapfpffapmjgojcnbfjonfjfg/related

(2) If you cannot see the Cookie-Button at the right top, then click the plugin button, and then enable "pinning" for the plugin "cookie.txt".

(3) Login to YouTube at the same network where the YouTube Downloader will run.
Press the cookie plugin button and copy the contents to a file called cookies.txt

(4) Edit cookies.txt and add following comment at the very top: `# HTTP Cookie File`

(5) Rename and move the file to **~/.yt_cookies**
OR
Add the argument `--cookies=cookies.txt` to ytdwn.

## License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by  the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
