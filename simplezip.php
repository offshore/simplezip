<?php

/**
 * SimpleZip - PHP zip archive creation class.
 * PHP Version 5
 * @package SimpleZip
 * @link https://github.com/offshore/simplezip/ GitHub
 * @link http://www.pkware.com/documents/casestudies/APPNOTE.TXT PKWare appnote
 * @link http://aopdg.ru/
 * @author Alex Offshore <offshore@aopdg.ru>
 * @note This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY
 */
class SimpleZip {
    const APPNOTE_VER = 63; //!< The version of appnote used to implement this code is 6.3.4

    const SIG_LH    = 0x04034b50; //!< Local header magic number
    const SIG_DD    = 0x08074b50; //!< Data descriptor magic number
    const SIG_CD    = 0x02014b50; //!< Central directory magic number
    const SIG_EOCD  = 0x06054b50; //!< End of central directory magic number

    const FL_ENC        = 0x0001; //!< [_______________x] If set, indicates that the file is encrypted.
    const FLMASK_12     = 0x0006; //!< [_____________xx_] Mask: used only with methods 6, 8, 9 and 14, those bits are undefined if the compression method is any other.
    const FL_6_8K       = 0x0002; //!< [______________x_] Method 6: if set, indicates an 8K sliding dictionary was used. If clear, then a 4K sliding dictionary was used.
    const FL_6_SF       = 0x0004; //!< [_____________x__] Method 6: if set, indicates 3 Shannon-Fano trees were used to encode the sliding dictionary output. If clear, then 2 Shannon-Fano trees were used.
    const FL_8_NORM     = 0x0000; //!< [________________] Methods 8 and 9: Normal (-en) compression option was used.
    const FL_8_MAX      = 0x0002; //!< [______________x_] Methods 8 and 9: Maximum (-exx/-ex) compression option was used.
    const FL_8_FAST     = 0x0004; //!< [_____________x__] Methods 8 and 9: Fast (-ef) compression option was used.
    const FL_8_SFAST    = 0x0006; //!< [_____________xx_] Methods 8 and 9: Super Fast (-es) compression option was used.
    const FL_14_EOS     = 0x0002; //!< [______________x_] Method 14: if set, indicates an end-of-stream (EOS) marker is used to mark the end of the compressed data stream. If clear, then an EOS marker is not present and the compressed data size must be known to extract.
    const FL_ONEPASS    = 0x0008; //!< [____________x___] If set, the fields <crc-32>, <compressed size> and <uncompressed size> are set to zero in the local header. The correct values are put in the data descriptor immediately following the compressed data. (Note: PKZIP version 2.04g for DOS only recognizes this bit for method 8 compression, newer versions of PKZIP recognize this bit for any compression method.)
    const FL_RESERVED4  = 0x0010; //!< [___________x____] Reserved for use with method 8, for enhanced deflating.
    const FL_PATCHDATA  = 0x0020; //!< [__________x_____] If set, indicates that the file is compressed patched data. (Note: Requires PKZIP version 2.70 or greater)
    const FL_ENC_STRONG = 0x0040; //!< [_________x______] Strong encryption. If this bit is set, you MUST set the version needed to extract value to at least 50 and you MUST also set bit 0. If AES encryption is used, the version needed to extract value MUST be at least 51. See the section describing the Strong Encryption Specification for details. Refer to the section in this document entitled "Incorporating PKWARE Proprietary Technology into Your Product" for more information.
    const FL_RESERVED7  = 0x0080; //!< [________x_______] Currently unused.
    const FL_RESERVED8  = 0x0100; //!< [_______x________] Currently unused.
    const FL_RESERVED9  = 0x0200; //!< [______x_________] Currently unused.
    const FL_RESERVED10 = 0x0400; //!< [_____x__________] Currently unused.
    const FL_UTF8       = 0x0800; //!< [____x___________] Language encoding flag (EFS). If this bit is set, the filename and comment fields for this file MUST be encoded using UTF-8. (see APPENDIX D)
    const FL_RESERVED12 = 0x1000; //!< [___x____________] Reserved by PKWARE for enhanced compression.
    const FL_LH_HIDDEN  = 0x2000; //!< [__x_____________] Set when encrypting the Central Directory to indicate selected data values in the Local Header are masked to hide their actual values. See the section describing the Strong Encryption Specification for details. Refer to the section in this document entitled "Incorporating PKWARE Proprietary Technology into Your Product" for more information.
    const FL_RESERVED14 = 0x4000; //!< [_x______________] Reserved by PKWARE.
    const FL_RESERVED15 = 0x8000; //!< [x_______________] Reserved by PKWARE.

    const CM_DEFAULT    = 0; //!< Compression method by default
    const CM_STORE      = 0; //!< The file is stored (no compression)
    const CM_SHRINK     = 1; //!< The file is Shrunk
    const CM_REDUCE_1   = 2; //!< The file is Reduced with compression factor 1
    const CM_REDUCE_2   = 3; //!< The file is Reduced with compression factor 2
    const CM_REDUCE_3   = 4; //!< The file is Reduced with compression factor 3
    const CM_REDUCE_4   = 5; //!< The file is Reduced with compression factor 4
    const CM_IMPLODE    = 6; //!< The file is Imploded
    const CM_7          = 7; //!< Reserved for Tokenizing compression algorithm
    const CM_DEFLATE    = 8; //!< The file is Deflated
    const CM_DEFLATE64  = 9; //!< Enhanced Deflating using Deflate64(tm)
    const CM_PKIMPLODE  = 10; //!< PKWARE Data Compression Library Imploding (old IBM TERSE)
    const CM_11         = 11; //!< Reserved by PKWARE
    const CM_BZIP2      = 12; //!< File is compressed using BZIP2 algorithm
    const CM_13         = 13; //!< Reserved by PKWARE
    const CM_LZMA       = 14; //!< LZMA (EFS)
    const CM_15         = 15; //!< Reserved by PKWARE
    const CM_16         = 16; //!< Reserved by PKWARE
    const CM_17         = 17; //!< Reserved by PKWARE
    const CM_TERSE      = 18; //!< File is compressed using IBM TERSE
    const CM_LZ77       = 19; //!< IBM LZ77 z Architecture (PFS)
    const CM_WAVPACK    = 97; //!< WavPack compressed data
    const CM_PPMD       = 98; //!< PPMd version I, Rev 1

    protected static $methods = array(
        self::CM_DEFLATE => array('zlib.deflate', 'zlib.inflate'),
        self::CM_DEFLATE64 => array('zlib.deflate', 'zlib.gzinflate'),
    );

    const VER_DOS       = 0; //!< MS-DOS and OS/2 (FAT / VFAT / FAT32 file systems)
    const VER_AMIGA     = 1; //!< Amiga
    const VER_OPENVMS   = 2; //!< OpenVMS
    const VER_UNIX      = 3; //!< UNIX
    const VER_VMCMS     = 4; //!< VM/CMS
    const VER_ATARI     = 5; //!< Atari ST
    const VER_OS2       = 6; //!< OS/2 H.P.F.S.
    const VER_MACINTOSH = 7; //!< Macintosh
    const VER_ZSYSTEM   = 8; //!< Z-System
    const VER_CPM       = 9; //!< CP/M
    const VER_WINDOWS   = 10; //!< Windows NTFS
    const VER_MVS       = 11; //!< MVS (OS/390 - Z/OS)
    const VER_VSE       = 12; //!< VSE
    const VER_ACORN     = 13; //!< Acorn Risc
    const VER_VFAT      = 14; //!< VFAT
    const VER_AMVS      = 15; //!< alternate MVS
    const VER_BEOS      = 16; //!< BeOS
    const VER_TANDEM    = 17; //!< Tandem
    const VER_OS400     = 18; //!< OS/400
    const VER_OSX       = 19; //!< OS X (Darwin)

    // When using ZIP64 extensions, the corresponding value in the zip64 end of central directory record MUST also be set. This field should be set appropriately to indicate whether Version 1 or Version 2 format is in use.
    const FT_DEFAULT    = 10; //!< Default value
    const FT_VLABEL     = 11; //!< File is a volume label
    const FT_FOLDER     = 20; //!< File is a folder (directory)
    const FT_DEFLATE    = 20; //!< File is compressed using Deflate compression
    const FT_ENC        = 20; //!< File is encrypted using traditional PKWARE encryption
    const FT_DEFLATE64  = 21; //!< File is compressed using Deflate64(tm)
    const FT_IMPLODE    = 25; //!< File is compressed using PKWARE DCL Implode
    const FT_PATCHDATA  = 27; //!< File is a patch data set
    const FT_ZIP64      = 45; //!< File uses ZIP64 format extensions
    const FT_BZIP2      = 46; //!< File is compressed using BZIP2 compression // Early 7.x (pre-7.2) versions of PKZIP incorrectly set the version needed to extract for BZIP2 compression to be 50 when it should have been 46.
    const FT_DES        = 50; //!< File is encrypted using DES
    const FT_3DES       = 50; //!< File is encrypted using 3DES
    const FT_RC2        = 50; //!< File is encrypted using original RC2 encryption
    const FT_RC4        = 50; //!< File is encrypted using RC4 encryption
    const FT_AES        = 51; //!< File is encrypted using AES encryption
    const FT_RC2X       = 51; //!< File is encrypted using corrected RC2 encryption // Refer to the section on Strong Encryption Specification for additional information regarding RC2 corrections.
    const FT_RC2X64     = 52; //!< File is encrypted using corrected RC2-64 encryption // Refer to the section on Strong Encryption Specification for additional information regarding RC2 corrections.
    const FT_NON_OEAP   = 61; //!< File is encrypted using non-OAEP key wrapping // Certificate encryption using non-OAEP key wrapping is the intended mode of operation for all versions beginning with 6.1. Support for OAEP key wrapping MUST only be used for backward compatibility when sending ZIP files to be opened by versions of PKZIP older than 6.1 (5.0 or 6.0).
    const FT_CDENC      = 62; //!< Central directory encryption
    const FT_LZMA       = 63; //!< File is compressed using LZMA
    const FT_PPMD       = 63; //!< File is compressed using PPMd // Files compressed using PPMd MUST set the version needed to extract field to 6.3, however, not all ZIP programs enforce this and may be unable to decompress data files compressed using PPMd if this value is set.
    const FT_BLOWFISH   = 63; //!< File is encrypted using Blowfish
    const FT_TWOFISH    = 63; //!< File is encrypted using Twofish

    protected static $struct_local_header = array(
        'sig'       => 'V', // 0    4    Local file header signature = 0x04034b50 (read as a little-endian number)
        'vermin'    => 'v', // 4    2    Version needed to extract (minimum)
        'flags'     => 'v', // 6    2    General purpose bit flag
        'method'    => 'v', // 8    2    Compression method
        'mtime'     => 'v', //10    2    File last modification time
        'mdate'     => 'v', //12    2    File last modification date
        'crc32'     => 'V', //14    4    CRC-32
        'csize'     => 'V', //18    4    Compressed size
        'usize'     => 'V', //22    4    Uncompressed size
        'fnlen'     => 'v', //26    2    File name length (n)
        'extralen'  => 'v', //28    2    Extra field length (m)
        'fn'        => 'a*',//30    n    File name
        'extra'     => 'a*',//30+n  m    Extra field
    );
    protected static $struct_data_descriptor = array(
        'sig'       => 'V', // 0    0/4    Optional data descriptor signature = 0x08074b50
        'crc32'     => 'V', // 0/4    4    CRC-32
        'csize'     => 'V', // 4/8    4    Compressed size
        'usize'     => 'V', // 8/12   4    Uncompressed size
    );
    protected static $struct_central_directory = array(
        'sig'       => 'V', // 0    4    Central directory file header signature = 0x02014b50
        'vermade'   => 'v', // 4    2    Version made by
        'vermin'    => 'v', // 6    2    Version needed to extract (minimum)
        'flags'     => 'v', // 8    2    General purpose bit flag
        'method'    => 'v', //10    2    Compression method
        'mtime'     => 'v', //12    2    File last modification time
        'mdate'     => 'v', //14    2    File last modification date
        'crc32'     => 'V', //16    4    CRC-32
        'csize'     => 'V', //20    4    Compressed size
        'usize'     => 'V', //24    4    Uncompressed size
        'fnlen'     => 'v', //28    2    File name length (n)
        'extralen'  => 'v', //30    2    Extra field length (m)
        'infolen'   => 'v', //32    2    File comment length (k)
        'fvolnum'   => 'v', //34    2    Disk number where file starts
        'ifa'       => 'v', //36    2    Internal file attributes
        'efa'       => 'V', //38    4    External file attributes
        'lhoffset'  => 'V', //42    4    Relative offset of local file header. This is the number of
                            //           bytes between the start of the first disk on which the file occurs,
                            //           and the start of the local file header. This allows software reading
                            //           the central directory to locate the position of the file inside the .ZIP file.
        'fn'        => 'a*',//46    n    File name
        'extra'     => 'a*',//46+n  m    Extra field
        'info'      => 'a*',//46+n+m k   File comment
    );
    protected static $struct_end_of_central_directory = array(
        'sig'       => 'V', // 0    4    End of central directory signature = 0x06054b50
        'volnum'    => 'v', // 4    2    Number of this disk
        'cdvolnum'  => 'v', // 6    2    Disk where central directory starts
        'volcdcount'=> 'v', // 8    2    Number of central directory records on this disk
        'cdcount'   => 'v', //10    2    Total number of central directory records
        'cdlen'     => 'V', //12    4    Size of central directory (bytes)
        'cdstart'   => 'V', //16    4    Offset of start of central directory, relative to start of archive
        'infolen'   => 'v', //20    2    Comment length (n)
        'info'      => 'a*',//22    n    Comment
    );

    protected static function dosdate($ut) {
        $year   = date('Y', $ut) - 1980;
        $month  = date('n', $ut);
        $day    = date('j', $ut);
        return (($day & 31)) | (($month << 5) & 480) | (($year << 9) & 65024);
    }
    protected static function dostime($ut) {
        $hour   = date('G', $ut);
        $min    = (int)date('i', $ut);
        $sec    = (int)date('s', $ut);
        return (floor($sec / 2) & 31) | (($min << 5) & 2016) | (($hour << 11) & 63488);
    }

    /**
    * Create ZIP archive
    *
    * @param mixed $target путь к zip-файлу или FALSE для выдачи в stdout
    * @param mixed $files ключ - имя в архиве, значение -- исходный файл или array(fn, mtime, method, info)
    * @param mixed $opts array(info, method)
    * @param mixed $error
    */
    public static function create($target, $files = array(), $opts = array(), &$error = false) {
        $error = false;
        $files_list = array(); // prepared files metadata
        do {
            if ($target === false) {
                $f = STDOUT;
            } elseif (!($f = fopen($target, 'wb'))) {
                $error = 'Target archive cannot be initialized';
                break;
            }
            $CD_buf = ''; // central directory buffer
            $pos = 0; // current pointer position (not using ftell because $f may be STDOUT)
            // prepare End Of Central Directory structure
            $EOCD = array(
                'sig'       => self::SIG_EOCD,
                'volnum'    => 0,
                'cdvolnum'  => 0,
                'volcdcount'=> 0,
                'cdcount'   => 0,
                'cdlen'     => 0,
                'cdstart'   => 0,
                'infolen'   => strlen($opts['info']),
                'info'      => iconv('utf8', 'CP866', (string)$opts['info']),
            );
            // check and lock files first
            foreach ($files as $name => $file) {
                if (!is_array($file)) $file = array('src' => $file);
                $file['fn'] = $name;
                if (!($file['f'] = fopen($file['src'], 'rb'))) {
                    $error = "Unable to open file $file[src]";
                    break;
                }
                if (!flock($file['f'], LOCK_SH)) {
                    $error = "Unable to lock file $file[src]";
                    break;
                }
                $file['time'] = isset($file['time']) ? $file['time'] : filemtime($file['src']);
                $file['size'] = filesize($file['src']);
                $files_list[] = $file;
            }
            foreach ($files_list as $file) {
                //$fn = iconv('utf8', 'CP866', $name);
                $method = $opts['method'] ?: self::CM_DEFAULT;
                $vermin = self::FT_DEFAULT;

                $LH_pos = $pos; // position to save in Central Directory
                // prepare and write local header for this file
                $LH = array(
                    'sig'       => self::SIG_LH,
                    'vermin'    => $vermin,
                    'flags'     => self::FL_UTF8 | self::FL_ONEPASS,
                    'method'    => $method,
                    'mtime'     => self::dostime($file['time']),
                    'mdate'     => self::dosdate($file['time']),
                    'crc32'     => 0, // will save this later in Data Descriptor
                    'csize'     => 0, // this too
                    'usize'     => 0, // and this
                    'fnlen'     => strlen($file['fn']),
                    'extralen'  => 0,
                    'fn'        => $file['fn'],
                    'extra'     => '',
                );
                $LH_buf = '';
                foreach (self::$struct_local_header as $field => $type) $LH_buf .= pack($type, $LH[$field]);
                $pos += fwrite($f, $LH_buf);

                // process data
                $hash_ctx = hash_init('crc32b');
                $usize = 0; // uncompressed size counter
                $csize = 0; // compressed size counter
                // create the passthrough filter that counts uncompressed size of input file and it's crc32 hash
                $filter_passthru = stream_filter_append($file['f'], SIMPLEZIP_PTF, STREAM_FILTER_READ, function(&$data) use (&$hash_ctx, &$usize) {
                    hash_update($hash_ctx, $data);
                    $usize += strlen($data);
                });
                $filter_compress = false;
                if ($method !== self::CM_STORE) {
                    if (!isset(self::$methods[$method])) {
                        $error = "Method $method compression is not supported";
                        break;
                    }
                    // this stream filter will do the compressing job according to method selected
                    $filter_compress = stream_filter_append($file['f'], self::$methods[$method][0], STREAM_FILTER_READ);
                }
                while (($data = fread($file['f'], 8192))) $csize += fwrite($f, $data); // pass the data through counter and (optional) compression stream filters
                if ($filter_compress) stream_filter_remove($filter_compress); // remove compression filter
                stream_filter_remove($filter_passthru);
                $crc32 = hexdec(hash_final($hash_ctx)); // finalize hash //TODO check 32-bit PHP
                $pos += $csize; // increase target position counter

                // write Data Descriptor
                $DD = array(
                    'sig'       => self::SIG_DD,
                    'crc32'     => $crc32,
                    'csize'     => $csize,
                    'usize'     => $usize,
                );
                $DD_buf = '';
                foreach (self::$struct_data_descriptor as $field => $type) $DD_buf .= pack($type, $DD[$field]);
                $pos += fwrite($f, $DD_buf);

                // save Central Directory entry to buffer
                $CD = array(
                    'sig'       => self::SIG_CD,
                    'vermade'   => (self::APPNOTE_VER << 8) | self::VER_DOS,
                    'vermin'    => $vermin,
                    'flags'     => self::FL_UTF8 | self::FL_ONEPASS,
                    'method'    => $opts['method'] ?: self::CM_DEFAULT,
                    'mtime'     => $LH['mtime'],
                    'mdate'     => $LH['mdate'],
                    'crc32'     => $DD['crc32'],
                    'csize'     => $DD['csize'],
                    'usize'     => $DD['usize'],
                    'fnlen'     => strlen($file['fn']),
                    'extralen'  => 0,
                    'infolen'   => 0,
                    'fvolnum'   => 0,
                    'ifa'       => 0,
                    'efa'       => 0,
                    'lhoffset'  => $LH_pos,
                    'fn'        => $file['fn'],
                    'extra'     => '',
                    'info'      => '',
                );
                foreach (self::$struct_central_directory as $field => $type) $CD_buf .= pack($type, $CD[$field]);

                $EOCD['volcdcount']++;
                $EOCD['cdcount']++;
            }
            // finalize EOCD data
            $EOCD['cdlen'] = strlen($CD_buf);
            $EOCD['cdstart'] = $pos;
            // save Central Directory records
            $pos += fwrite($f, $CD_buf);
            // finally, save EOCD
            $EOCD_buf = '';
            foreach (self::$struct_end_of_central_directory as $field => $type) $EOCD_buf .= pack($type, $EOCD[$field]);
            $pos += fwrite($f, $EOCD_buf);
        } while (0);
        if ($f && ($f !== STDOUT)) fclose($f);
        // cleanup
        foreach ($files_list as $file) {
            flock($file['f'], LOCK_UN);
            fclose($file['f']);
        }
        return $error ? false : true;
    }
}

/**
* SimpleZip_passthru helper class to integrate with php stream filtering
*/
class SimpleZip_passthru extends php_user_filter {
    public $params;
    public $filtername;

    function filter($in, $out, &$consumed, $closing) {
        while ($bucket = stream_bucket_make_writeable($in)) {
            call_user_func_array($this->params, array(&$bucket->data));
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}

if (!defined('SIMPLEZIP_PTF')) define('SIMPLEZIP_PTF', 'simplezip.passthru');
stream_filter_register(SIMPLEZIP_PTF, 'SimpleZip_passthru');
