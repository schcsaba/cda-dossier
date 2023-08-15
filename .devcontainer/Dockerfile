FROM qmcgaw/latexdevcontainer

RUN apt-get update
RUN apt-get install -y python3 python3-venv python3-pip
RUN python3 -m pip install --upgrade pip setuptools wheel
RUN pip install Pygments

RUN tlmgr install babel-french && \
    tlmgr install appendix && \
    tlmgr install babel-hungarian && \
    tlmgr install titlesec && \
    tlmgr install minted && \
    tlmgr install tabularray && \
    tlmgr install ninecolors && \
    tlmgr install hyphen-english && \
    tlmgr install hyphen-french  && \
    tlmgr install hyphen-hungarian