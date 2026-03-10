#from moviepy.editor import VideoFileClip, CompositeAudioClip, AudioFileClip, TextClip, CompositeVideoClip
from exp222 import UREDI, vrati_fakt, vrati_text1, vrati_text2
import random
from moviepy.editor import *


count = 1
lista1 = [1,2,3,4,5,6,7,8,9,10]
lista2 = [1,2,3,4,5,6,7,8,9,10]
while(count<=10):
    rand_video = random.choice(lista1)
    clip = VideoFileClip(f"videa/{rand_video}.mp4").subclip(0, 12)

    # choice = random.choice(lista) ### ODI U DRUG FILE ISPROBAJ TAA RABOTA
    rand_audio = random.choice(lista2)
    audioo = AudioFileClip(f"audio/{rand_audio}.mp3").subclip(0, 12)

    fakttt = vrati_fakt()
    fakttt = fakttt.upper()
    print(fakttt)
    broj_bukvi = 0
    for i in fakttt:
        broj_bukvi+=53
    FAKT = TextClip(fakttt, fontsize=125, color="white", bg_color="black", font="Agency-FB-Bold").set_duration(12).margin(left=23, right=23)
    FAKT = FAKT.set_position((1080/2 - broj_bukvi/2, 440))

    text11 = vrati_text1()
    print(text11)

    text22 = vrati_text2()
    print(text22)

    TEXT1 = TextClip(text11, fontsize=130, color="white", font="Agency-FB-Bold", stroke_width=4, stroke_color='black', size=(1080,1920), method="caption")
    TEXT1 = TEXT1.set_position('center')
    TEXT1 = TEXT1.set_start(0)
    TEXT1 = TEXT1.set_end(6.5)

    TEXT2 = TextClip(text22, fontsize=130, color="white", font="Agency-FB-Bold", stroke_width=4, stroke_color='black', size=(1080,1920), method="caption")
    TEXT2 = TEXT2.set_position('center')
    TEXT2 = TEXT2.set_start(7)
    TEXT2 = TEXT2.set_end(12)

    clip.audio = CompositeAudioClip([audioo])

    clip = CompositeVideoClip([clip, FAKT, TEXT1, TEXT2])

    expName = ""
    if fakttt == "DEEP FACT":
        expName = "Deep fact. #shorts #deep #psychologyfacts " + str(count)
    elif fakttt == "BOY FACT":
        expName = "Boy fact. #shorts #boy #psychologyfacts " + str(count)
    elif fakttt == "GIRL FACT":
        expName = "Girl fact. #shorts #girlfact #psychologyfacts " + str(count)
    elif fakttt == "DREAM FACT":
        expName = "Dream fact. #shorts #dream #psychologyfacts " + str(count)
    elif fakttt == "RELATIONSHIP FACT":
        expName = "Relationship fact. #shorts #relationship #psychologyfacts " + str(count)
    elif fakttt == "SAD FACT":
        expName = "Sad fact. #shorts #sad #psychologyfacts " + str(count)
    elif fakttt == "PSYCHOLOGY FACT":
        expName = "Psychology fact. #shorts #psychology #psychologyfacts " + str(count)

    clip.write_videofile(f"exported/{expName}.mp4")
    count+=1
    UREDI()
    lista1.remove(rand_video)
    lista2.remove(rand_audio)